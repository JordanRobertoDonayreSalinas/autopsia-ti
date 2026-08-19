import 'dart:convert';

/// Espejo de la tabla `mon_monitoreo_modulos` de Laravel: el módulo fijo
/// RR.HH. y los consultorios dinámicos que el auditor va agregando
/// libremente durante la visita (ver app/Models/MonitoreoModulos.php y
/// MonitoreoModuloGenericController).
class MonitoreoModulo {
  // --- Campo local de la cola offline (no existe en Laravel) ---
  final String actaOfflineId;

  // --- Espejo de mon_monitoreo_modulos ---
  final int? id;
  final int? cabeceraMonitoreoId; // se resuelve al sincronizar la acta
  final String moduloNombre; // ej. 'triaje', 'medicina', 'infraestructura'
  final String contenido; // JSON (texto) con las respuestas del formulario
  final String? pdfFirmadoPath;

  MonitoreoModulo({
    required this.actaOfflineId,
    this.id,
    this.cabeceraMonitoreoId,
    required this.moduloNombre,
    required this.contenido,
    this.pdfFirmadoPath,
  });

  factory MonitoreoModulo.fromJson(Map<String, dynamic> json) {
    return MonitoreoModulo(
      actaOfflineId: json['acta_offline_id'] ?? '',
      id: json['id'],
      cabeceraMonitoreoId: json['cabecera_monitoreo_id'],
      moduloNombre: json['modulo_nombre'] ?? '',
      contenido: json['contenido'] is String ? json['contenido'] : (json['contenido']?.toString() ?? '{}'),
      pdfFirmadoPath: json['pdf_firmado_path'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'acta_offline_id': actaOfflineId,
      if (id != null) 'id': id,
      'cabecera_monitoreo_id': cabeceraMonitoreoId,
      'modulo_nombre': moduloNombre,
      'contenido': contenido,
      'pdf_firmado_path': pdfFirmadoPath,
    };
  }

  /// Slugs fijos que Laravel reconoce por nombre exacto (ver
  /// ActaRepository.moduloRRHH / moduloInfraestructura2D).
  static const _modulosFijos = ['rrhh', 'infraestructura_2d'];

  /// Payload que espera cada elemento del arreglo `consultorios` en
  /// POST /v1/sync (ver OfflineSyncController::sincronizarLoteOffline).
  ///
  /// Para los módulos fijos, 'titulo_consultorio' debe ser el slug exacto
  /// ('rrhh') para que el backend lo reconozca. Para los consultorios
  /// dinámicos, el slug local (ej. 'triaje_1755412345') es solo un
  /// identificador de cola offline — el backend genera su propio slug
  /// final a partir del título legible, así que hay que enviar el título
  /// real guardado en contenido['titulo_consultorio'], no el slug local.
  Map<String, dynamic> toSyncPayload() {
    String titulo = moduloNombre;
    if (!_modulosFijos.contains(moduloNombre)) {
      try {
        final data = jsonDecode(contenido) as Map<String, dynamic>;
        final tituloGuardado = (data['titulo_consultorio'] as String?)?.trim();
        if (tituloGuardado != null && tituloGuardado.isNotEmpty) {
          titulo = tituloGuardado;
        }
      } catch (_) {}
    }
    return {
      'titulo_consultorio': titulo,
      'contenido': contenido,
    };
  }

  factory MonitoreoModulo.fromMap(Map<String, dynamic> map) => MonitoreoModulo.fromJson(map);
}
