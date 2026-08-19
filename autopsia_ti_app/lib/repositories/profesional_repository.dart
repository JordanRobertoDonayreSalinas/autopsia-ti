import '../models/profesional.dart';
import '../services/offline_db_service.dart';

/// Acceso a los profesionales registrados para firma/entrevistas en campo.
class ProfesionalRepository {
  final OfflineDbService _db;

  ProfesionalRepository({OfflineDbService? db}) : _db = db ?? OfflineDbService.instance;

  Future<Profesional?> buscarPorDni(String dni) => _db.buscarProfesionalPorDni(dni);
}
