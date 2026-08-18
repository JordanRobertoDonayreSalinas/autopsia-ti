import '../models/reunion.dart';
import '../services/offline_db_service.dart';

/// Acceso a actas de reunión capturadas en campo (SQLite local).
class ReunionRepository {
  final OfflineDbService _db;

  ReunionRepository({OfflineDbService? db}) : _db = db ?? OfflineDbService.instance;

  Future<List<Reunion>> obtenerTodas() => _db.obtenerReuniones();

  Future<void> guardar(Reunion reunion) => _db.guardarReunion(reunion);
}
