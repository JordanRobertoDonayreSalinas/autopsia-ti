/// Espejo de la tabla `mon_equipos_computo` de Laravel (ver
/// storage/app/database_schema.json). Los nombres de campo son exactamente
/// los del $fillable de app/Models/EquipoComputo.php.
class EquipoComputo {
  // --- Campo local de la cola offline (no existe en Laravel) ---
  final String actaOfflineId; // asocia el equipo con la acta antes de sincronizar

  // --- Espejo de mon_equipos_computo ---
  final int? id;
  final int? cabeceraMonitoreoId; // se resuelve al sincronizar la acta
  final String modulo; // consultorio/módulo al que pertenece el equipo
  final String descripcion;
  final int cantidad;
  final String estado; // 'Operativo' por defecto en Laravel
  final String? nroSerie;
  final String? propio; // 'ESTABLECIMIENTO' | 'PERSONAL' | 'SERVICIO'
  final String? observacion;

  EquipoComputo({
    required this.actaOfflineId,
    this.id,
    this.cabeceraMonitoreoId,
    required this.modulo,
    required this.descripcion,
    this.cantidad = 1,
    this.estado = 'Operativo',
    this.nroSerie,
    this.propio,
    this.observacion,
  });

  factory EquipoComputo.fromJson(Map<String, dynamic> json) {
    return EquipoComputo(
      actaOfflineId: json['acta_offline_id'] ?? '',
      id: json['id'],
      cabeceraMonitoreoId: json['cabecera_monitoreo_id'],
      modulo: json['modulo'] ?? '',
      descripcion: json['descripcion'] ?? '',
      cantidad: json['cantidad'] is int ? json['cantidad'] : int.tryParse('${json['cantidad']}') ?? 1,
      estado: json['estado'] ?? 'Operativo',
      nroSerie: json['nro_serie'],
      propio: json['propio'],
      observacion: json['observacion'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'acta_offline_id': actaOfflineId,
      if (id != null) 'id': id,
      'cabecera_monitoreo_id': cabeceraMonitoreoId,
      'modulo': modulo,
      'descripcion': descripcion,
      'cantidad': cantidad,
      'estado': estado,
      'nro_serie': nroSerie,
      'propio': propio,
      'observacion': observacion,
    };
  }

  /// Payload que espera cada elemento del arreglo `equipos` dentro de un
  /// consultorio en POST /v1/sync (ver OfflineSyncController).
  Map<String, dynamic> toSyncPayload() {
    return {
      'descripcion': descripcion,
      'cantidad': cantidad,
      'estado': estado,
      'propio': propio,
      'nro_serie': nroSerie,
      'observacion': observacion,
    };
  }

  factory EquipoComputo.fromMap(Map<String, dynamic> map) => EquipoComputo.fromJson(map);
}
