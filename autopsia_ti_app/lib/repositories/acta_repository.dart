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

  /// Guarda en el dispositivo una acta capturada en campo junto con los
  /// módulos evaluados, el equipo de cómputo inventariado y el personal
  /// presente. Queda en estado 'pending' hasta la próxima sincronización.
  Future<void> guardarActaCompleta(
    CabeceraMonitoreo acta, {
    List<MonitoreoModulo> modulos = const [],
    List<EquipoComputo> equipos = const [],
    List<EquipoMonitoreo> equipoMonitoreo = const [],
  }) =>
      _db.guardarActa(acta, modulos: modulos, equipos: equipos, equipoMonitoreo: equipoMonitoreo);

  Future<List<Map<String, dynamic>>> obtenerPendientes() => _db.obtenerActasPendientes();

  Future<CabeceraMonitoreo?> obtenerPorOfflineId(String offlineId) => _db.obtenerActaPorOfflineId(offlineId);

  /// Slug fijo del módulo RR.HH. — debe coincidir exactamente con
  /// modulo_nombre='rrhh' en Laravel (RecursosHumanosController) para que el
  /// backend lo reconozca como el mismo módulo al sincronizar, en vez de
  /// tratarlo como un consultorio ad-hoc con nombre aleatorio.
  static const moduloRRHH = 'rrhh';

  Future<MonitoreoModulo?> obtenerModuloRRHH(String actaOfflineId) => _db.obtenerModulo(actaOfflineId, moduloRRHH);

  Future<List<MonitoreoModulo>> obtenerModulos(String actaOfflineId) => _db.obtenerModulosPorActa(actaOfflineId);

  Future<List<EquipoComputo>> obtenerEquipos(String actaOfflineId) => _db.obtenerEquiposPorActa(actaOfflineId);

  Future<void> guardarModuloRRHH(String actaOfflineId, String contenidoJson) => _db.guardarOActualizarModulo(
        MonitoreoModulo(actaOfflineId: actaOfflineId, moduloNombre: moduloRRHH, contenido: contenidoJson),
      );

  /// Envía el lote pendiente a POST /v1/sync y marca como sincronizado en el
  /// dispositivo solo lo que el servidor confirmó explícitamente.
  Future<Map<String, dynamic>> sincronizarPendientes() => _sync.sincronizarPendientes();
}
