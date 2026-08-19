import 'dart:convert';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_computo.dart';
import '../models/equipo_monitoreo.dart';
import '../models/monitoreo_modulo.dart';
import '../services/offline_db_service.dart';
import '../services/sync_service.dart';

/// Decide de dónde vienen y hacia dónde van las actas de monitoreo
/// (borrador local en SQLite vs sincronización con Laravel).
class ActaRepository {
  final OfflineDbService _db;
  final SyncService _sync;

  ActaRepository({OfflineDbService? db, SyncService? sync})
      : _db = db ?? OfflineDbService.instance,
        _sync = sync ?? SyncService();

  /// Guarda en el dispositivo una acta capturada en campo junto con el
  /// personal presente. Queda en estado 'pending' hasta la próxima
  /// sincronización. Los módulos/consultorios se agregan después, uno por
  /// uno, desde la pantalla de detalle de acta (igual que en la web).
  Future<void> guardarActaCompleta(
    CabeceraMonitoreo acta, {
    List<EquipoMonitoreo> equipoMonitoreo = const [],
  }) =>
      _db.guardarActa(acta, equipoMonitoreo: equipoMonitoreo);

  Future<List<Map<String, dynamic>>> obtenerPendientes() => _db.obtenerActasPendientes();

  Future<CabeceraMonitoreo?> obtenerPorOfflineId(String offlineId) => _db.obtenerActaPorOfflineId(offlineId);

  /// Busca, entre las actas capturadas en ESTE dispositivo, la que ya
  /// sincronizó con el id real del servidor — para poder abrir su detalle
  /// (RR.HH./consultorios/croquis) desde el listado real de actas. Si el
  /// acta fue creada en otro dispositivo, no hay nada local que abrir.
  Future<CabeceraMonitoreo?> obtenerPorIdServidor(int idServidor) async {
    final todas = await _db.obtenerActas();
    final match = todas.where((a) => a.id == idServidor);
    return match.isEmpty ? null : match.first;
  }

  /// Listado real de actas (todas las del sistema, según rol) — espejo de
  /// MonitoreoController::index. Requiere conexión.
  Future<Map<String, dynamic>> obtenerActasMonitoreo(Map<String, String> filtros) => _sync.obtenerActasMonitoreo(filtros);

  Future<Map<String, dynamic>> anularActaMonitoreo(int id) => _sync.anularActaMonitoreo(id);

  /// Descarga el detalle completo de una acta que YA EXISTE en el servidor
  /// (se haya creado en Laravel o en otro dispositivo) y la hidrata en
  /// SQLite local para poder editarla como cualquier otra acta del
  /// dispositivo. Devuelve el offline_id local (determinístico: 'SRV-{id}')
  /// o null si falló (sin conexión, sin acceso, etc.).
  Future<String?> descargarActaServidor(int id) async {
    final res = await _sync.obtenerDetalleActaServidor(id);
    if (res['success'] != true) return null;

    final actaJson = Map<String, dynamic>.from(res['acta']);
    final offlineId = 'SRV-$id';
    final acta = CabeceraMonitoreo.fromJson({
      ...actaJson,
      'offline_id': offlineId,
      'sync_status': 'synced',
      'local_created_at': DateTime.now().toIso8601String(),
    });

    final modulos = List<Map<String, dynamic>>.from(res['modulos'] ?? []).map((m) => MonitoreoModulo(
          actaOfflineId: offlineId,
          moduloNombre: m['modulo_nombre'],
          contenido: jsonEncode(m['contenido'] ?? {}),
          pdfFirmadoPath: m['pdf_firmado_path'],
        ));

    final equipoMonitoreo = List<Map<String, dynamic>>.from(res['equipo_monitoreo'] ?? []).map((p) => EquipoMonitoreo(
          actaOfflineId: offlineId,
          tipoDoc: p['tipo_doc'] ?? 'DNI',
          doc: p['doc'] ?? '',
          apellidoPaterno: p['apellido_paterno'],
          apellidoMaterno: p['apellido_materno'],
          nombres: p['nombres'],
          cargo: p['cargo'],
          institucion: p['institucion'],
        ));

    final equipos = List<Map<String, dynamic>>.from(res['equipos'] ?? []).map((e) => EquipoComputo(
          actaOfflineId: offlineId,
          modulo: (e['modulo'] ?? '').toString().toLowerCase(),
          descripcion: e['descripcion'] ?? '',
          cantidad: e['cantidad'] ?? 1,
          estado: e['estado'] ?? 'Operativo',
          nroSerie: e['nro_serie'],
          propio: e['propio'],
          observacion: e['observacion'],
        ));

    await _db.hidratarActaServidor(acta, modulos: modulos.toList(), equipoMonitoreo: equipoMonitoreo.toList(), equipos: equipos.toList());
    return offlineId;
  }

  /// Si la acta ya tiene un id real de servidor (fue descargada con
  /// descargarActaServidor() o ya completó su sincronización inicial), el
  /// módulo se guarda TAMBIÉN en el servidor de inmediato — no espera al
  /// próximo autoSync, porque ese solo procesa actas nuevas todavía
  /// 'pending' (ver sincronizarPendientes/OfflineSyncController). Si no hay
  /// id de servidor (acta recién creada offline) o no hay conexión, el
  /// guardado local ya hecho por el llamador es suficiente por ahora.
  Future<void> _sincronizarModuloSiCorresponde(String actaOfflineId, String moduloNombre, String contenidoJson) async {
    final acta = await _db.obtenerActaPorOfflineId(actaOfflineId);
    if (acta?.id == null) return;

    final equipos = await _db.obtenerEquiposPorModulo(actaOfflineId, moduloNombre);
    await _sync.guardarModuloServidor(
      acta!.id!,
      moduloNombre,
      jsonDecode(contenidoJson),
      equipos.map((e) => e.toSyncPayload()).toList(),
    );
  }

  /// Slugs fijos que Laravel reconoce por nombre exacto (no deben tratarse
  /// como consultorios ad-hoc). Ver MonitoreoController::gestionarModulos.
  static const moduloRRHH = 'rrhh';
  static const moduloInfraestructura2D = 'infraestructura_2d';

  Future<MonitoreoModulo?> obtenerModuloRRHH(String actaOfflineId) => _db.obtenerModulo(actaOfflineId, moduloRRHH);

  Future<List<MonitoreoModulo>> obtenerModulos(String actaOfflineId) => _db.obtenerModulosPorActa(actaOfflineId);

  /// Los consultorios que el auditor va agregando libremente durante la
  /// visita (ej. "Triaje", "Farmacia") — todo lo que no sea un módulo fijo.
  Future<List<MonitoreoModulo>> obtenerConsultoriosDinamicos(String actaOfflineId) async {
    final modulos = await obtenerModulos(actaOfflineId);
    return modulos.where((m) => m.moduloNombre != moduloRRHH && m.moduloNombre != moduloInfraestructura2D).toList();
  }

  Future<List<EquipoComputo>> obtenerEquipos(String actaOfflineId) => _db.obtenerEquiposPorActa(actaOfflineId);

  Future<List<EquipoComputo>> obtenerEquiposDeModulo(String actaOfflineId, String moduloNombre) =>
      _db.obtenerEquiposPorModulo(actaOfflineId, moduloNombre);

  /// Reemplaza los equipos de un módulo y, si la acta ya tiene id de
  /// servidor, vuelve a empujar módulo+equipos completos de inmediato — las
  /// pantallas de edición guardan primero el contenido y después los
  /// equipos en una llamada aparte, así que este es el punto que ve el
  /// estado final real de ambos.
  Future<void> reemplazarEquiposDeModulo(String actaOfflineId, String moduloNombre, List<EquipoComputo> equipos) async {
    await _db.reemplazarEquiposDeModulo(actaOfflineId, moduloNombre, equipos);
    final modulo = await _db.obtenerModulo(actaOfflineId, moduloNombre);
    if (modulo != null) {
      await _sincronizarModuloSiCorresponde(actaOfflineId, moduloNombre, modulo.contenido);
    }
  }

  Future<void> guardarModuloRRHH(String actaOfflineId, String contenidoJson) async {
    await _db.guardarOActualizarModulo(MonitoreoModulo(actaOfflineId: actaOfflineId, moduloNombre: moduloRRHH, contenido: contenidoJson));
    await _sincronizarModuloSiCorresponde(actaOfflineId, moduloRRHH, contenidoJson);
  }

  Future<MonitoreoModulo?> obtenerModuloInfraestructura2D(String actaOfflineId) => _db.obtenerModulo(actaOfflineId, moduloInfraestructura2D);

  Future<void> guardarModuloInfraestructura2D(String actaOfflineId, String contenidoJson) async {
    await _db.guardarOActualizarModulo(MonitoreoModulo(actaOfflineId: actaOfflineId, moduloNombre: moduloInfraestructura2D, contenido: contenidoJson));
    await _sincronizarModuloSiCorresponde(actaOfflineId, moduloInfraestructura2D, contenidoJson);
  }

  /// Crea un consultorio dinámico nuevo con nombre libre — espejo de
  /// MonitoreoModuloGenericController::crearConsultorio: el usuario escribe
  /// el título y el sistema genera un slug técnico único (slug_timestamp).
  /// Devuelve el slug generado para poder navegar al formulario.
  Future<String> crearConsultorio(String actaOfflineId, String tituloLibre) async {
    final titulo = tituloLibre.trim().toUpperCase();
    final slugBase = _slug(titulo);
    final slug = '${slugBase}_${DateTime.now().millisecondsSinceEpoch ~/ 1000}';

    final contenido = jsonEncode({
      'titulo_consultorio': titulo,
      'fecha': DateTime.now().toString().split(' ')[0],
      'turno': 'MAÑANA',
    });

    await _db.guardarOActualizarModulo(
      MonitoreoModulo(actaOfflineId: actaOfflineId, moduloNombre: slug, contenido: contenido),
    );
    return slug;
  }

  Future<void> guardarConsultorio(String actaOfflineId, String moduloNombre, String contenidoJson) async {
    await _db.guardarOActualizarModulo(MonitoreoModulo(actaOfflineId: actaOfflineId, moduloNombre: moduloNombre, contenido: contenidoJson));
    await _sincronizarModuloSiCorresponde(actaOfflineId, moduloNombre, contenidoJson);
  }

  Future<void> eliminarConsultorio(String actaOfflineId, String moduloNombre) =>
      _db.eliminarModulo(actaOfflineId, moduloNombre);

  String _slug(String texto) {
    final normalizado = texto
        .toLowerCase()
        .replaceAll(RegExp(r'[áàäâ]'), 'a')
        .replaceAll(RegExp(r'[éèëê]'), 'e')
        .replaceAll(RegExp(r'[íìïî]'), 'i')
        .replaceAll(RegExp(r'[óòöô]'), 'o')
        .replaceAll(RegExp(r'[úùüû]'), 'u')
        .replaceAll('ñ', 'n');
    return normalizado.replaceAll(RegExp(r'[^a-z0-9]+'), '_').replaceAll(RegExp(r'^_+|_+$'), '');
  }

  /// Envía el lote pendiente a POST /v1/sync y marca como sincronizado en el
  /// dispositivo solo lo que el servidor confirmó explícitamente.
  Future<Map<String, dynamic>> sincronizarPendientes() => _sync.sincronizarPendientes();
}
