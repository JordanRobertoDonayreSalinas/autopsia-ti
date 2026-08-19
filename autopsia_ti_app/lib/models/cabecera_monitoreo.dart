/// Espejo de la tabla `mon_cabecera_monitoreo` de Laravel (ver
/// storage/app/database_schema.json). Los nombres de campo son exactamente
/// los del $fillable de app/Models/CabeceraMonitoreo.php.
class CabeceraMonitoreo {
  // --- Campos locales de la cola offline (no existen en Laravel) ---
  final String offlineId; // identificador local antes de sincronizar
  final String syncStatus; // 'pending' | 'synced'
  final String localCreatedAt; // fecha/hora de captura en el dispositivo

  // --- Espejo de mon_cabecera_monitoreo ---
  final int? id; // asignado por Laravel al sincronizar
  final int? userId; // lo asigna el backend (auth()->id()) al sincronizar
  final int establecimientoId;
  final String tipoOrigen; // 'ESTANDAR' o 'ESPECIALIZADA'
  final int? numeroActa; // correlativo: lo calcula el servidor
  final String? categoriaCongelada;
  final String? responsableCongelado;
  final String fecha;
  final String responsable;
  final String implementador;
  final String pozoTierra; // 'SI' | 'NO'
  final int? pozoTierraCantidad;
  final int? pozoTierraOperativos;
  final int? pozoTierraInoperativos;
  final String panelSolar; // 'SI' | 'NO'
  final int? panelSolarCantidad;
  final int? panelSolarOperativos;
  final int? panelSolarInoperativos;
  final String? foto1;
  final String? foto2;
  final bool firmado;
  final bool anulado;
  final String? firmadoPdf;

  CabeceraMonitoreo({
    required this.offlineId,
    this.syncStatus = 'pending',
    required this.localCreatedAt,
    this.id,
    this.userId,
    required this.establecimientoId,
    this.tipoOrigen = 'ESTANDAR',
    this.numeroActa,
    this.categoriaCongelada,
    this.responsableCongelado,
    required this.fecha,
    required this.responsable,
    required this.implementador,
    this.pozoTierra = 'NO',
    this.pozoTierraCantidad,
    this.pozoTierraOperativos,
    this.pozoTierraInoperativos,
    this.panelSolar = 'NO',
    this.panelSolarCantidad,
    this.panelSolarOperativos,
    this.panelSolarInoperativos,
    this.foto1,
    this.foto2,
    this.firmado = false,
    this.anulado = false,
    this.firmadoPdf,
  });

  factory CabeceraMonitoreo.fromJson(Map<String, dynamic> json) {
    return CabeceraMonitoreo(
      offlineId: json['offline_id'] ?? '',
      syncStatus: json['sync_status'] ?? 'pending',
      localCreatedAt: json['local_created_at'] ?? json['created_at'] ?? '',
      id: json['id'],
      userId: json['user_id'],
      establecimientoId: json['establecimiento_id'] is int
          ? json['establecimiento_id']
          : int.tryParse('${json['establecimiento_id']}') ?? 0,
      tipoOrigen: json['tipo_origen'] ?? 'ESTANDAR',
      numeroActa: json['numero_acta'],
      categoriaCongelada: json['categoria_congelada'],
      responsableCongelado: json['responsable_congelado'],
      fecha: json['fecha'] ?? '',
      responsable: json['responsable'] ?? '',
      implementador: json['implementador'] ?? '',
      pozoTierra: json['pozo_tierra'] ?? 'NO',
      pozoTierraCantidad: json['pozo_tierra_cantidad'],
      pozoTierraOperativos: json['pozo_tierra_operativos'],
      pozoTierraInoperativos: json['pozo_tierra_inoperativos'],
      panelSolar: json['panel_solar'] ?? 'NO',
      panelSolarCantidad: json['panel_solar_cantidad'],
      panelSolarOperativos: json['panel_solar_operativos'],
      panelSolarInoperativos: json['panel_solar_inoperativos'],
      foto1: json['foto1'],
      foto2: json['foto2'],
      firmado: json['firmado'] == true || json['firmado'] == 1,
      anulado: json['anulado'] == true || json['anulado'] == 1,
      firmadoPdf: json['firmado_pdf'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'offline_id': offlineId,
      'sync_status': syncStatus,
      'local_created_at': localCreatedAt,
      if (id != null) 'id': id,
      'user_id': userId,
      'establecimiento_id': establecimientoId,
      'tipo_origen': tipoOrigen,
      'numero_acta': numeroActa,
      'categoria_congelada': categoriaCongelada,
      'responsable_congelado': responsableCongelado,
      'fecha': fecha,
      'responsable': responsable,
      'implementador': implementador,
      'pozo_tierra': pozoTierra,
      'pozo_tierra_cantidad': pozoTierraCantidad,
      'pozo_tierra_operativos': pozoTierraOperativos,
      'pozo_tierra_inoperativos': pozoTierraInoperativos,
      'panel_solar': panelSolar,
      'panel_solar_cantidad': panelSolarCantidad,
      'panel_solar_operativos': panelSolarOperativos,
      'panel_solar_inoperativos': panelSolarInoperativos,
      'foto1': foto1,
      'foto2': foto2,
      'firmado': firmado ? 1 : 0,
      'anulado': anulado ? 1 : 0,
      'firmado_pdf': firmadoPdf,
    };
  }

  /// Payload que Laravel realmente espera por acta en POST /v1/sync
  /// (ver OfflineSyncController::sincronizarLoteOffline). Los campos locales
  /// (offline_id, sync_status, id, user_id, numero_acta) no se envían: el id
  /// y el correlativo los asigna el servidor.
  Map<String, dynamic> toSyncPayload() {
    return {
      'establecimiento_id': establecimientoId,
      'fecha': fecha,
      'responsable': responsable,
      'implementador': implementador,
      'tipo_origen': tipoOrigen,
    };
  }

  factory CabeceraMonitoreo.fromMap(Map<String, dynamic> map) => CabeceraMonitoreo.fromJson(map);
}
