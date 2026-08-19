import 'dart:convert';
import 'dart:io';
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
      final res = await http.get(Uri.parse('$baseUrl/version')).timeout(const Duration(seconds: 3));
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

      // Mientras el acta está 'pending', foto1/foto2 guardan la ruta local
      // del archivo elegido (no una ruta de storage de Laravel, que solo
      // existe después de sincronizar) — igual que en reuniones.
      String? foto1Base64;
      String? foto2Base64;
      try {
        if (acta.foto1 != null && acta.foto1!.isNotEmpty && File(acta.foto1!).existsSync()) {
          foto1Base64 = base64Encode(await File(acta.foto1!).readAsBytes());
        }
        if (acta.foto2 != null && acta.foto2!.isNotEmpty && File(acta.foto2!).existsSync()) {
          foto2Base64 = base64Encode(await File(acta.foto2!).readAsBytes());
        }
      } catch (_) {}

      payload.add({
        'offline_id': acta.offlineId,
        ...acta.toSyncPayload(),
        if (foto1Base64 != null) 'foto_1_base64': foto1Base64,
        if (foto2Base64 != null) 'foto_2_base64': foto2Base64,
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

  // 5. Obtener estadísticas reales del Dashboard desde MySQL (vía Laravel API).
  // 'anio' espeja el filtro acumulativo (<= año) de UsuarioController::index.
  Future<Map<String, dynamic>> getDashboardStats({String anio = 'todos'}) async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/dashboard/stats?anio=$anio'))
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

  // 6. Obtener marcadores del mapa (establecimientos con lat/long reales),
  // cada uno con 'etapa' (0=sin diagnóstico, 1=con diagnóstico) igual que
  // mapa_progresion.blade.php, y los años disponibles para el filtro "Año".
  Future<Map<String, dynamic>> getMapMarkers({String anio = 'todos'}) async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/establecimientos/map?anio=$anio'))
          .timeout(const Duration(seconds: 6));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          return {
            'markers': List<Map<String, dynamic>>.from(data['markers'] ?? []),
            'anios_disponibles': List<dynamic>.from(data['anios_disponibles'] ?? []).map((a) => a.toString()).toList(),
          };
        }
      }
    } catch (_) {}
    return {'markers': <Map<String, dynamic>>[], 'anios_disponibles': <String>[]};
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

  // 8. Buscar documento (DNI) para autocompletar el formulario de nuevo
  // usuario — espejo de AdminController::buscarDni / UsersApiController::buscarDni.
  Future<Map<String, dynamic>> buscarDni(String tipoDoc, String dni, {bool localOnly = false}) async {
    try {
      final qs = 'tipo_doc=$tipoDoc&dni=$dni${localOnly ? '&local_only=1' : ''}';
      final res = await http
          .get(Uri.parse('$baseUrl/users/buscar-dni?$qs'), headers: await _authHeaders())
          .timeout(const Duration(seconds: 8));
      if (res.statusCode == 200) return json.decode(res.body);
    } catch (_) {}
    return {'exists': false, 'exists_external': false};
  }

  // 9. Crear usuario — espejo de AdminController::usersStore / UsersApiController::store.
  Future<Map<String, dynamic>> crearUsuario(Map<String, dynamic> payload) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl/users'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode(payload),
          )
          .timeout(const Duration(seconds: 8));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}', 'errors': data['errors']};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 10. Editar usuario — espejo de AdminController::usersUpdate / UsersApiController::update.
  Future<Map<String, dynamic>> actualizarUsuario(int id, Map<String, dynamic> payload) async {
    try {
      final res = await http
          .put(
            Uri.parse('$baseUrl/users/$id'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode(payload),
          )
          .timeout(const Duration(seconds: 8));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}', 'errors': data['errors']};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 11. Activar/Bloquear usuario — espejo de AdminController::toggleStatus / UsersApiController::toggleStatus.
  Future<Map<String, dynamic>> toggleUsuarioStatus(int id) async {
    try {
      final res = await http
          .patch(Uri.parse('$baseUrl/users/$id/toggle-status'), headers: await _authHeaders())
          .timeout(const Duration(seconds: 8));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}'};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12b. Editar establecimiento — espejo de EstablecimientoController::update
  // / EstablecimientosApiController::update. Requiere conexión (igual que
  // Gestionar Usuarios): es el catálogo maestro compartido con todo el
  // sistema, no un borrador de campo.
  Future<Map<String, dynamic>> actualizarEstablecimiento(int id, Map<String, dynamic> payload) async {
    try {
      final res = await http
          .put(
            Uri.parse('$baseUrl/establecimientos/$id'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode(payload),
          )
          .timeout(const Duration(seconds: 10));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}', 'errors': data['errors']};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12c. Consultar RENIPRESS/SUSALUD para un establecimiento — espejo de
  // EstablecimientoController::consultarRenipress. Consulta en vivo a un
  // servicio externo, puede tardar varios segundos.
  Future<Map<String, dynamic>> consultarRenipressEstablecimiento(int id) async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/establecimientos/$id/consultar-renipress'), headers: await _authHeaders())
          .timeout(const Duration(seconds: 25));
      final data = json.decode(res.body);
      if (res.statusCode == 200 && data['ok'] == true) return {'success': true, 'datos': data['datos']};
      return {'success': false, 'message': data['mensaje'] ?? 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.'};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12g. Detalle completo de una acta ya existente en el servidor (creada en
  // Laravel o en otro dispositivo) — para poder descargarla y editarla,
  // igual que la propia decisión del usuario: "debo poder editar el acta se
  // haya creado en Laravel o en Flutter, la única condición es el acceso".
  Future<Map<String, dynamic>> obtenerDetalleActaServidor(int id) async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/monitoreo/$id'), headers: await _authHeaders()).timeout(const Duration(seconds: 12));
      final data = json.decode(res.body);
      if (res.statusCode == 200 && data['success'] == true) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}'};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12h. Guarda un módulo directamente contra una acta ya existente en el
  // servidor (no pasa por la cola offline de creación). Se usa cuando el
  // usuario edita una acta que descargó (ver 12g) o que ya terminó su
  // sincronización inicial.
  Future<Map<String, dynamic>> guardarModuloServidor(int actaId, String moduloNombre, Map<String, dynamic> contenido, List<Map<String, dynamic>> equipos) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl/monitoreo/$actaId/modulos'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode({'modulo_nombre': moduloNombre, 'contenido': contenido, 'equipos': equipos}),
          )
          .timeout(const Duration(seconds: 10));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}'};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12e. Listado real de "Actas de Diagnóstico Situacional" — espejo de
  // MonitoreoController::index. `filtros` acepta las mismas claves que la
  // web: implementador, provincia, distrito, establecimiento_id, estado
  // ('firmada'|'pendiente'), estado_anulado ('activo'|'anulado'|'todos'),
  // fecha_inicio, fecha_fin, page.
  Future<Map<String, dynamic>> obtenerActasMonitoreo(Map<String, String> filtros) async {
    try {
      final uri = Uri.parse('$baseUrl/monitoreo').replace(queryParameters: filtros);
      final res = await http.get(uri, headers: await _authHeaders()).timeout(const Duration(seconds: 10));
      if (res.statusCode == 200) return json.decode(res.body);
    } catch (_) {}
    return {'success': false, 'actas': [], 'counts': {'completados': 0, 'pendientes': 0, 'anuladas': 0}, 'filtros': {}};
  }

  // 12f. Anular/Reactivar un acta de monitoreo ya sincronizada.
  Future<Map<String, dynamic>> anularActaMonitoreo(int id) async {
    try {
      final res = await http.post(Uri.parse('$baseUrl/monitoreo/$id/anular'), headers: await _authHeaders()).timeout(const Duration(seconds: 8));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}'};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12d. Editar Mi Perfil — espejo de UsuarioController::perfilUpdate /
  // PerfilApiController::update. Cualquier usuario autenticado edita su
  // propio registro (sin importar el rol), a diferencia de Gestionar
  // Usuarios. Requiere conexión: no hay edición de perfil offline.
  Future<Map<String, dynamic>> actualizarPerfil(Map<String, dynamic> payload) async {
    try {
      final res = await http
          .put(
            Uri.parse('$baseUrl/perfil'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode(payload),
          )
          .timeout(const Duration(seconds: 10));
      final data = json.decode(res.body);
      if (res.statusCode == 200) return data;
      return {'success': false, 'message': data['message'] ?? 'El servidor respondió HTTP ${res.statusCode}', 'errors': data['errors']};
    } catch (e) {
      return {'success': false, 'message': 'Sin conexión con el servidor: $e'};
    }
  }

  // 12. Sincronizar actas de reunión capturadas offline — espejo de
  // sincronizarPendientes() pero para el módulo de reuniones.
  Future<Map<String, dynamic>> sincronizarReuniones(List<Map<String, dynamic>> reuniones) async {
    if (reuniones.isEmpty) {
      return {'success': true, 'sincronizados': 0, 'errores': <Map<String, dynamic>>[]};
    }
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl/reuniones/sync'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', ...await _authHeaders()},
            body: json.encode({'reuniones': reuniones}),
          )
          .timeout(const Duration(seconds: 20));
      if (res.statusCode == 200) return json.decode(res.body);
      return {
        'success': false,
        'sincronizados': 0,
        'errores': [
          {'message': 'El servidor respondió HTTP ${res.statusCode}'}
        ],
      };
    } catch (e) {
      return {
        'success': false,
        'sincronizados': 0,
        'errores': [
          {'message': 'Sin conexión con el servidor: $e'}
        ],
      };
    }
  }
}
