import '../models/establecimiento.dart';
import '../services/offline_db_service.dart';
import '../services/sync_service.dart';

/// Decide de dónde vienen los establecimientos (SQLite local vs API Laravel)
/// para que las pantallas no llamen directamente a los servicios.
class EstablecimientoRepository {
  final OfflineDbService _db;
  final SyncService _sync;

  EstablecimientoRepository({OfflineDbService? db, SyncService? sync})
      : _db = db ?? OfflineDbService.instance,
        _sync = sync ?? SyncService();

  Future<List<Establecimiento>> buscar(String term) => _db.buscarEstablecimientos(term);

  /// Catálogo completo (sin límite de 30) para el listado paginado de la
  /// pestaña "Establecimientos".
  Future<List<Establecimiento>> obtenerTodos() => _db.obtenerTodosLosEstablecimientos();

  /// Descarga el catálogo real desde Laravel (GET /v1/catalog) y lo persiste localmente.
  Future<bool> descargarCatalogo() => _sync.descargarCatalogo();

  /// Guarda cambios de edición contra el servidor (requiere conexión, igual
  /// que Gestionar Usuarios) y, si el servidor confirma, refresca la copia
  /// local con los datos ya persistidos.
  Future<Map<String, dynamic>> actualizar(int id, Map<String, dynamic> payload) async {
    final res = await _sync.actualizarEstablecimiento(id, payload);
    if (res['success'] == true && res['establecimiento'] != null) {
      final actualizado = Establecimiento.fromJson(res['establecimiento']);
      final todos = await obtenerTodos();
      final idx = todos.indexWhere((e) => e.id == id);
      if (idx != -1) {
        todos[idx] = actualizado;
        await _db.guardarCatalogo(todos);
      }
    }
    return res;
  }

  Future<Map<String, dynamic>> consultarRenipress(int id) => _sync.consultarRenipressEstablecimiento(id);
}
