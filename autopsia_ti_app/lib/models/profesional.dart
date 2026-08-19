/// Espejo de la tabla `mon_profesionales` de Laravel (ver
/// storage/app/database_schema.json). Los nombres de campo son exactamente
/// los del $fillable de app/Models/Profesional.php.
///
/// Nota: 'profesion', 'colegio_profesional' y 'nro_colegiatura' NO existen en
/// este modelo en Laravel (colegio_profesional/colegiatura son campos de
/// Establecimiento) — ver Informe de revisión, sección 3.4.
class Profesional {
  final int? id;
  final String? tipoDoc;
  final String doc;
  final String? apellidoPaterno;
  final String? apellidoMaterno;
  final String? nombres;
  final String? email;
  final String? telefono;
  final String? cargo;
  final String? firmaPath;
  final String tipoFirma; // 'MANUAL' por defecto en Laravel
  final String? ultimaActualizacionFirma;

  Profesional({
    this.id,
    this.tipoDoc,
    required this.doc,
    this.apellidoPaterno,
    this.apellidoMaterno,
    this.nombres,
    this.email,
    this.telefono,
    this.cargo,
    this.firmaPath,
    this.tipoFirma = 'MANUAL',
    this.ultimaActualizacionFirma,
  });

  String get nombreCompleto => '${nombres ?? ''} ${apellidoPaterno ?? ''} ${apellidoMaterno ?? ''}'.trim();

  factory Profesional.fromJson(Map<String, dynamic> json) {
    return Profesional(
      id: json['id'],
      tipoDoc: json['tipo_doc'],
      doc: json['doc'] ?? '',
      apellidoPaterno: json['apellido_paterno'],
      apellidoMaterno: json['apellido_materno'],
      nombres: json['nombres'],
      email: json['email'],
      telefono: json['telefono'],
      cargo: json['cargo'],
      firmaPath: json['firma_path'],
      tipoFirma: json['tipo_firma'] ?? 'MANUAL',
      ultimaActualizacionFirma: json['ultima_actualizacion_firma'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      if (id != null) 'id': id,
      'tipo_doc': tipoDoc,
      'doc': doc,
      'apellido_paterno': apellidoPaterno,
      'apellido_materno': apellidoMaterno,
      'nombres': nombres,
      'email': email,
      'telefono': telefono,
      'cargo': cargo,
      'firma_path': firmaPath,
      'tipo_firma': tipoFirma,
      'ultima_actualizacion_firma': ultimaActualizacionFirma,
    };
  }

  factory Profesional.fromMap(Map<String, dynamic> map) => Profesional.fromJson(map);
}
