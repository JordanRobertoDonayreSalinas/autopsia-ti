import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/establecimiento.dart';
import 'offline_db_service.dart';

class SyncService {
  final String baseUrl;

  SyncService({this.baseUrl = 'https://autopsia-ti.systemperu.digital/api/v1'});

  /// Cabecera Authorization: Bearer <token> para los endpoints protegidos con
  /// Sanctum (hoy solo POST /sync). El token se guarda en shared_preferences
  /// tras un login online exitoso — ver LoginScreen._handleLogin.
  Future<Map<String, String>> _authHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    return token == null ? {} : {'Authorization': 'Bearer $token'};
  }

  // 1. Verificar versión del sistema y catálogo
  Future<Map<String, dynamic>> checkVersion() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/version')).timeout(const Duration(milliseconds: 1200));
      if (res.statusCode == 200) {
        return json.decode(res.body);
      }
    } catch (_) {}
    return {'success': false, 'message': 'Dispositivo en Modo Campo Offline'};
  }

  // 2. Descargar catálogo de IPRESS y guardar en SQLite
  Future<bool> descargarCatalogo() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/catalog')).timeout(const Duration(seconds: 3));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          final List listJson = data['establecimientos'] ?? [];
          final establecimientos = listJson.map((e) => Establecimiento.fromJson(e)).toList();
          await OfflineDbService.instance.guardarCatalogo(establecimientos);
          return true;
        }
      }
    } catch (_) {}
    return false;
  }

  // 3. Sincronizar lotes de actas capturadas offline hacia Laravel.
  //
  // Construye exactamente la forma que espera
  // OfflineSyncController::sincronizarLoteOffline (establecimiento_id, fecha,
  // responsable, implementador, tipo_origen, equipo_monitoreo[], consultorios[]
  // con contenido y su propio equipos[]) y solo marca como sincronizado en el
  // dispositivo lo que el servidor confirmó por su cuenta (offline_id -> id
  // real + numero_acta), nunca por asumir que el HTTP 200 implica éxito total.
  Future<Map<String, dynamic>> sincronizarPendientes() async {
    final db = OfflineDbService.instance;
    final pendientesMap = await db.obtenerActasPendientes();
    if (pendientesMap.isEmpty) {
      return {'success': true, 'sincronizados': 0, 'errores': <Map<String, dynamic>>[]};
    }

    final payload = <Map<String, dynamic>>[];
    for (final actaMap in pendientesMap) {
      final acta = CabeceraMonitoreo.fromMap(actaMap);
      final modulos = await db.obtenerModulosPorActa(acta.offlineId);
      final equipoMonitoreo = await db.obtenerEquipoMonitoreoPorActa(acta.offlineId);
      final equiposTodos = await db.obtenerEquiposPorActa(acta.offlineId);

      final consultorios = modulos.map((m) {
        final equiposDelModulo = equiposTodos
            .where((e) => e.modulo == m.moduloNombre)
            .map((e) => e.toSyncPayload())
            .toList();
        return {...m.toSyncPayload(), 'equipos': equiposDelModulo};
      }).toList();

      payload.add({
        'offline_id': acta.offlineId,
        ...acta.toSyncPayload(),
        'equipo_monitoreo': equipoMonitoreo
            .map((p) => {
                  'tipo_doc': p.tipoDoc,
                  'doc': p.doc,
                  'apellido_paterno': p.apellidoPaterno,
                  'apellido_materno': p.apellidoMaterno,
                  'nombres': p.nombres,
                  'cargo': p.cargo,
                  'institucion': p.institucion,
                })
            .toList(),
        'consultorios': consultorios,
      });
    }

    // Reintentos con backoff exponencial (1s, 2s, 4s) para fallas de red
    // transitorias — antes un solo intento fallido perdía el lote en silencio.
    const maxIntentos = 3;
    for (var intento = 1; intento <= maxIntentos; intento++) {
      try {
        final res = await http
            .post(
              Uri.parse('$baseUrl/sync'),
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...await _authHeaders(),
              },
              body: json.encode({'actas': payload}),
            )
            .timeout(const Duration(seconds: 15));

        if (res.statusCode == 401) {
          return {
            'success': false,
            'sincronizados': 0,
            'errores': [
              {'message': 'Sesión expirada: vuelva a iniciar sesión con conexión a internet.'}
            ],
          };
        }

        if (res.statusCode == 200) {
          final data = json.decode(res.body);
          final actasConfirmadas = List<Map<String, dynamic>>.from(data['actas'] ?? []);
          for (final a in actasConfirmadas) {
            await db.marcarActaSincronizada(
              a['offline_id'],
              id: a['id'],
              numeroActa: a['numero_acta'],
            );
          }
          final errores = List<Map<String, dynamic>>.from(data['errores'] ?? []);
          return {
            'success': errores.isEmpty,
            'sincronizados': actasConfirmadas.length,
            'errores': errores,
          };
        }

        return {
          'success': false,
          'sincronizados': 0,
          'errores': [
            {'message': 'El servidor respondió HTTP ${res.statusCode}'}
          ],
        };
      } catch (e) {
        if (intento == maxIntentos) {
          return {
            'success': false,
            'sincronizados': 0,
            'errores': [
              {'message': 'Sin conexión con el servidor: $e'}
            ],
          };
        }
        await Future.delayed(Duration(seconds: 1 << (intento - 1)));
      }
    }

    return {
      'success': false,
      'sincronizados': 0,
      'errores': [
        {'message': 'No se pudo sincronizar tras $maxIntentos intentos'}
      ],
    };
  }

  // 4. Autenticación oficial contra el Backend Laravel Autopsia TI (Sanctum).
  //
  // Ya NO existe un fallback que deje entrar con cualquier usuario/contraseña
  // cuando falla la red: eso era una puerta de acceso sin control. Si la
  // petición no puede completarse, se devuelve 'offline: true' para que la
  // pantalla de login decida si permite reingresar con la sesión en caché de
  // un usuario que ya se validó con éxito antes (ver LoginScreen._handleLogin).
  Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final payload = json.encode({'username': username, 'password': password});
      final headers = {'Content-Type': 'application/json', 'Accept': 'application/json'};

      final res = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: headers,
        body: payload,
      ).timeout(const Duration(seconds: 6));

      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        return {'success': true, 'token': data['token'], 'user': data['user']};
      } else if (res.statusCode == 422 || res.statusCode == 401) {
        final data = json.decode(res.body);
        return {'success': false, 'message': data['message'] ?? 'Credenciales incorrectas'};
      }
      return {'success': false, 'message': 'El servidor respondió HTTP ${res.statusCode}'};
    } catch (e) {
      return {'success': false, 'offline': true, 'message': 'Sin conexión con el servidor'};
    }
  }

  // 5. Obtener estadísticas reales del Dashboard desde MySQL (vía Laravel API)
  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/dashboard/stats'))
          .timeout(const Duration(seconds: 4));
      if (res.statusCode == 200) {
        return json.decode(res.body);
      }
    } catch (_) {}
    // Sin conexión: no se fabrican cifras, el dashboard debe mostrar 0 hasta sincronizar.
    return {
      'success': false,
      'total_ipress': 0,
      'sin_diagnostico': 0,
      'con_diagnostico': 0,
    };
  }

  // 6. Obtener marcadores del mapa (establecimientos con lat/long reales)
  Future<List<Map<String, dynamic>>> getMapMarkers() async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/establecimientos/map'))
          .timeout(const Duration(seconds: 6));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          return List<Map<String, dynamic>>.from(data['markers'] ?? []);
        }
      }
    } catch (_) {}
    return [];
  }

  // 7. Obtener lista de usuarios reales desde MySQL (vía Laravel API)
  Future<List<Map<String, dynamic>>> getUsers() async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/users'))
          .timeout(const Duration(seconds: 4));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          return List<Map<String, dynamic>>.from(data['users'] ?? []);
        }
      }
    } catch (_) {}
    return [];
  }
}
