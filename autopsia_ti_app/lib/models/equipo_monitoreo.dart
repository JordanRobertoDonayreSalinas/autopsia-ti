/// Espejo de la tabla `mon_equipo_monitoreo` de Laravel (personal del
/// establecimiento presente en la visita, ver storage/app/database_schema.json
/// y app/Models/MonitoreoEquipo.php). No tenía equivalente en Flutter — ver
/// Informe de revisión, sección 3.2.
class EquipoMonitoreo {
  // --- Campo local de la cola offline (no existe en Laravel) ---
  final String actaOfflineId;

  // --- Espejo de mon_equipo_monitoreo ---
  final int? id;
  final int? cabeceraMonitoreoId; // se resuelve al sincronizar la acta
  final String tipoDoc; // 'DNI' por defecto en Laravel
  final String doc;
  final String? apellidoPaterno;
  final String? apellidoMaterno;
  final String? nombres;
  final String cargo; // 'Implementador' por defecto en Laravel
  final String institucion;

  EquipoMonitoreo({
    required this.actaOfflineId,
    this.id,
    this.cabeceraMonitoreoId,
    this.tipoDoc = 'DNI',
    required this.doc,
    this.apellidoPaterno,
    this.apellidoMaterno,
    this.nombres,
    this.cargo = 'Implementador',
    required this.institucion,
  });

  factory EquipoMonitoreo.fromJson(Map<String, dynamic> json) {
    return EquipoMonitoreo(
      actaOfflineId: json['acta_offline_id'] ?? '',
      id: json['id'],
      cabeceraMonitoreoId: json['cabecera_monitoreo_id'],
      tipoDoc: json['tipo_doc'] ?? 'DNI',
      doc: json['doc'] ?? '',
      apellidoPaterno: json['apellido_paterno'],
      apellidoMaterno: json['apellido_materno'],
      nombres: json['nombres'],
      cargo: json['cargo'] ?? 'Implementador',
      institucion: json['institucion'] ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'acta_offline_id': actaOfflineId,
      if (id != null) 'id': id,
      'cabecera_monitoreo_id': cabeceraMonitoreoId,
      'tipo_doc': tipoDoc,
      'doc': doc,
      'apellido_paterno': apellidoPaterno,
      'apellido_materno': apellidoMaterno,
      'nombres': nombres,
      'cargo': cargo,
      'institucion': institucion,
    };
  }

  factory EquipoMonitoreo.fromMap(Map<String, dynamic> map) => EquipoMonitoreo.fromJson(map);
}
