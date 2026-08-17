import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/establecimiento.dart';
import 'offline_db_service.dart';

class SyncService {
  final String baseUrl;

  SyncService({this.baseUrl = 'https://autopsia-ti.systemperu.digital/api/v1'});

  // 1. Verificar versión del sistema y catálogo
  Future<Map<String, dynamic>> checkVersion() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/version')).timeout(const Duration(seconds: 3));
      if (res.statusCode == 200) {
        return json.decode(res.body);
      }
    } catch (e) {
      // Dispositivo offline
    }
    return {'success': false, 'message': 'Dispositivo en Modo Campo Offline'};
  }

  // 2. Descargar catálogo de 524 IPRESS y guardar en SQLite
  Future<bool> descargarCatalogo() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/catalog')).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          final List listJson = data['establecimientos'] ?? [];
          final establecimientos = listJson.map((e) => Establecimiento.fromJson(e)).toList();
          await OfflineDbService.instance.guardarCatalogo(establecimientos);
          return true;
        }
      }
    } catch (e) {
      // Offline
    }
    return false;
  }

  // 3. Sincronizar lotes de actas capturadas offline hacia Laravel cPanel
  Future<bool> sincronizarPendientes() async {
    try {
      final actas = await OfflineDbService.instance.obtenerActasPendientes();
      if (actas.isEmpty) return true;

      final res = await http.post(
        Uri.parse('$baseUrl/sync'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'actas': actas}),
      ).timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true) {
          await OfflineDbService.instance.limpiarSincronizados();
          return true;
        }
      }
    } catch (e) {
      // Error de red
    }
    return false;
  }
}
