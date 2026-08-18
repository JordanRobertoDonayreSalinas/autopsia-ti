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

  /// Descarga el catálogo real desde Laravel (GET /v1/catalog) y lo persiste localmente.
  Future<bool> descargarCatalogo() => _sync.descargarCatalogo();
}
