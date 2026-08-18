/// Espejo de la tabla `reuniones` de Laravel (ver
/// storage/app/database_schema.json). Los nombres de campo son exactamente
/// los del $fillable de app/Models/Reunion.php.
///
/// 'acuerdos', 'comentarios_observaciones' y 'participantes' son JSON
/// estructurado en Laravel: aquí se guardan como texto JSON crudo (tal cual
/// sqflite los persiste) en vez de aplanarlos a un string libre.
class Reunion {
  // --- Campos locales de la cola offline (no existen en Laravel) ---
  final String offlineId;
  final String syncStatus; // 'pending' | 'synced'
  final String localCreatedAt;

  // --- Espejo de reuniones ---
  final int? id;
  final String tituloReunion;
  final String fechaReunion;
  final String horaReunion;
  final String? horaFinalizadaReunion;
  final String nombreInstitucion;
  final String descripcionGeneral;
  final String? acuerdos; // JSON (texto)
  final String? comentariosObservaciones; // JSON (texto)
  final String? foto1;
  final String? foto2;
  final String? participantes; // JSON (texto)
  final String? archivoPdf;
  final bool firmado;
  final bool anulado;
  final String? asistenciaDesde;
  final String? asistenciaHasta;

  Reunion({
    required this.offlineId,
    this.syncStatus = 'pending',
    required this.localCreatedAt,
    this.id,
    required this.tituloReunion,
    required this.fechaReunion,
    required this.horaReunion,
    this.horaFinalizadaReunion,
    required this.nombreInstitucion,
    required this.descripcionGeneral,
    this.acuerdos,
    this.comentariosObservaciones,
    this.foto1,
    this.foto2,
    this.participantes,
    this.archivoPdf,
    this.firmado = false,
    this.anulado = false,
    this.asistenciaDesde,
    this.asistenciaHasta,
  });

  factory Reunion.fromJson(Map<String, dynamic> json) {
    return Reunion(
      offlineId: json['offline_id'] ?? '',
      syncStatus: json['sync_status'] ?? 'pending',
      localCreatedAt: json['local_created_at'] ?? json['created_at'] ?? '',
      id: json['id'],
      tituloReunion: json['titulo_reunion'] ?? '',
      fechaReunion: json['fecha_reunion'] ?? '',
      horaReunion: json['hora_reunion'] ?? '',
      horaFinalizadaReunion: json['hora_finalizada_reunion'],
      nombreInstitucion: json['nombre_institucion'] ?? '',
      descripcionGeneral: json['descripcion_general'] ?? '',
      acuerdos: json['acuerdos'] is String ? json['acuerdos'] : json['acuerdos']?.toString(),
      comentariosObservaciones: json['comentarios_observaciones'] is String
          ? json['comentarios_observaciones']
          : json['comentarios_observaciones']?.toString(),
      foto1: json['foto_1'],
      foto2: json['foto_2'],
      participantes: json['participantes'] is String ? json['participantes'] : json['participantes']?.toString(),
      archivoPdf: json['archivo_pdf'],
      firmado: json['firmado'] == true || json['firmado'] == 1,
      anulado: json['anulado'] == true || json['anulado'] == 1,
      asistenciaDesde: json['asistencia_desde'],
      asistenciaHasta: json['asistencia_hasta'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'offline_id': offlineId,
      'sync_status': syncStatus,
      'local_created_at': localCreatedAt,
      if (id != null) 'id': id,
      'titulo_reunion': tituloReunion,
      'fecha_reunion': fechaReunion,
      'hora_reunion': horaReunion,
      'hora_finalizada_reunion': horaFinalizadaReunion,
      'nombre_institucion': nombreInstitucion,
      'descripcion_general': descripcionGeneral,
      'acuerdos': acuerdos,
      'comentarios_observaciones': comentariosObservaciones,
      'foto_1': foto1,
      'foto_2': foto2,
      'participantes': participantes,
      'archivo_pdf': archivoPdf,
      'firmado': firmado ? 1 : 0,
      'anulado': anulado ? 1 : 0,
      'asistencia_desde': asistenciaDesde,
      'asistencia_hasta': asistenciaHasta,
    };
  }

  factory Reunion.fromMap(Map<String, dynamic> map) => Reunion.fromJson(map);
}
