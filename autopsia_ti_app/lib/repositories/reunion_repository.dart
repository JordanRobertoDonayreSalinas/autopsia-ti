import 'dart:convert';
import 'dart:io';
import '../models/reunion.dart';
import '../services/offline_db_service.dart';
import '../services/sync_service.dart';

/// Acceso a actas de reunión capturadas en campo (SQLite local) y su
/// sincronización con Laravel (POST /v1/reuniones/sync).
class ReunionRepository {
  final OfflineDbService _db;
  final SyncService _sync;

  ReunionRepository({OfflineDbService? db, SyncService? sync})
      : _db = db ?? OfflineDbService.instance,
        _sync = sync ?? SyncService();

  Future<List<Reunion>> obtenerTodas() => _db.obtenerReuniones();

  Future<List<Reunion>> obtenerPendientes() => _db.obtenerReunionesPendientes();

  Future<void> guardar(Reunion reunion) => _db.guardarReunion(reunion);

  /// Mientras una reunión está 'pending', foto_1/foto_2 guardan la ruta
  /// local del archivo elegido (no una ruta de storage de Laravel, que solo
  /// existe después de sincronizar) — aquí se leen esos archivos y se
  /// codifican en base64 para el envío en lote.
  Future<Map<String, dynamic>> sincronizarPendientes() async {
    final pendientes = await obtenerPendientes();
    if (pendientes.isEmpty) {
      return {'success': true, 'sincronizados': 0, 'errores': <Map<String, dynamic>>[]};
    }

    final payload = <Map<String, dynamic>>[];
    for (final r in pendientes) {
      String? foto1Base64;
      String? foto2Base64;
      try {
        if (r.foto1 != null && r.foto1!.isNotEmpty && File(r.foto1!).existsSync()) {
          foto1Base64 = base64Encode(await File(r.foto1!).readAsBytes());
        }
        if (r.foto2 != null && r.foto2!.isNotEmpty && File(r.foto2!).existsSync()) {
          foto2Base64 = base64Encode(await File(r.foto2!).readAsBytes());
        }
      } catch (_) {}

      payload.add({
        'offline_id': r.offlineId,
        'titulo_reunion': r.tituloReunion,
        'fecha_reunion': r.fechaReunion,
        'hora_reunion': r.horaReunion,
        'hora_finalizada_reunion': r.horaFinalizadaReunion,
        'nombre_institucion': r.nombreInstitucion,
        'descripcion_general': r.descripcionGeneral,
        'acuerdos': r.acuerdos != null ? json.decode(r.acuerdos!) : [],
        'comentarios_observaciones': r.comentariosObservaciones != null ? json.decode(r.comentariosObservaciones!) : [],
        'participantes': r.participantes != null ? json.decode(r.participantes!) : [],
        if (foto1Base64 != null) 'foto_1_base64': foto1Base64,
        if (foto2Base64 != null) 'foto_2_base64': foto2Base64,
      });
    }

    final resultado = await _sync.sincronizarReuniones(payload);
    final confirmadas = List<Map<String, dynamic>>.from(resultado['reuniones'] ?? []);
    for (final c in confirmadas) {
      await _db.marcarReunionSincronizada(c['offline_id'], id: c['id']);
    }
    return resultado;
  }
}
