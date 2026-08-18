/// Espejo de la tabla `mon_monitoreo_modulos` de Laravel (los hasta 18
/// módulos dinámicos evaluados por acta — RR.HH., Infraestructura,
/// Enfermería, etc. — ver app/Models/MonitoreoModulos.php). No tenía
/// equivalente en Flutter — ver Informe de revisión, sección 3.2.
///
/// La captura de los formularios de cada módulo es trabajo de Fase 5 del
/// plan; este modelo solo deja lista la tabla espejo para que ese trabajo
/// tenga dónde persistir localmente.
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

  /// Payload que espera cada elemento del arreglo `consultorios` en
  /// POST /v1/sync (ver OfflineSyncController::sincronizarLoteOffline).
  Map<String, dynamic> toSyncPayload() {
    return {
      'titulo_consultorio': moduloNombre,
      'contenido': contenido,
    };
  }

  factory MonitoreoModulo.fromMap(Map<String, dynamic> map) => MonitoreoModulo.fromJson(map);
}
