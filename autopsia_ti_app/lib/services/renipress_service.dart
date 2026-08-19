import 'dart:convert';
import 'package:http/http.dart' as http;

class RenipressService {
  final String baseUrl;

  RenipressService({this.baseUrl = 'https://autopsia-ti.systemperu.digital/api'});

  // Consultar datos de IPRESS en SUSALUD / RENIPRESS por código de 8 dígitos
  Future<Map<String, dynamic>> consultarDatosRenipress(String codigoIpress) async {
    final codigoLimpio = codigoIpress.trim();
    
    // Validar formato de 8 dígitos
    if (codigoLimpio.length != 8 || int.tryParse(codigoLimpio) == null) {
      return {
        'success': false,
        'message': 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.',
      };
    }

    try {
      final res = await http.post(
        Uri.parse('$baseUrl/renipress/consultar'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: json.encode({'codigo': codigoLimpio}),
      ).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        if (data['success'] == true && data['establecimiento'] != null) {
          return {
            'success': true,
            'nombre': data['establecimiento']['nombre'] ?? 'ESTABLECIMIENTO SIN NOMBRE',
            'estado': data['establecimiento']['estado'] ?? 'Activo',
            'categoria': data['establecimiento']['categoria'] ?? 'II-1',
            'direccion': data['establecimiento']['direccion'] ?? 'DIRECCIÓN REGISTRADA EN SUSALUD',
            'departamento': data['establecimiento']['departamento'] ?? 'LIMA',
            'provincia': data['establecimiento']['provincia'] ?? 'LIMA',
            'distrito': data['establecimiento']['distrito'] ?? 'LIMA',
          };
        }
      }
    } catch (_) {}

    return {
      'success': false,
      'message': 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.',
    };
  }
}
