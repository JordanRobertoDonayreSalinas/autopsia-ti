/// Un trabajador dentro del arreglo `trabajadores` que Laravel guarda en
/// mon_monitoreo_modulos.contenido para el módulo 'rrhh' (ver
/// RecursosHumanosController::store y resources/views/usuario/monitoreo/
/// modulos/rrhh.blade.php). Los nombres de campo son exactamente los que usa
/// ese formulario — no son un modelo Eloquent propio, es JSON anidado.
class TrabajadorRRHH {
  final String id; // ej. 'tr_<timestamp>_<random>', generado en el dispositivo
  final String servicio;
  final String tipoDoc; // 'DNI' | 'C.E.'
  final String doc;
  final String apellidoPaterno;
  final String? apellidoMaterno;
  final String nombres;
  final String profesion;
  final String? colegioProfesional;
  final String? colegiatura;
  final String? correo;
  final String? celular;
  final String? rne;
  final String esSerums; // 'SI' | 'NO'
  final String? periodoSerums;

  TrabajadorRRHH({
    required this.id,
    required this.servicio,
    this.tipoDoc = 'DNI',
    required this.doc,
    required this.apellidoPaterno,
    this.apellidoMaterno,
    required this.nombres,
    required this.profesion,
    this.colegioProfesional,
    this.colegiatura,
    this.correo,
    this.celular,
    this.rne,
    this.esSerums = 'NO',
    this.periodoSerums,
  });

  factory TrabajadorRRHH.fromJson(Map<String, dynamic> json) {
    return TrabajadorRRHH(
      id: json['id'] ?? '',
      servicio: json['servicio'] ?? '',
      tipoDoc: json['tipo_doc'] ?? 'DNI',
      doc: json['doc'] ?? '',
      apellidoPaterno: json['apellido_paterno'] ?? '',
      apellidoMaterno: json['apellido_materno'],
      nombres: json['nombres'] ?? '',
      profesion: json['profesion'] ?? '',
      colegioProfesional: json['colegio_profesional'],
      colegiatura: json['colegiatura'],
      correo: json['correo'],
      celular: json['celular'],
      rne: json['rne'],
      esSerums: json['es_serums'] ?? 'NO',
      periodoSerums: json['periodo_serums'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'servicio': servicio,
      'tipo_doc': tipoDoc,
      'doc': doc,
      'apellido_paterno': apellidoPaterno,
      'apellido_materno': apellidoMaterno,
      'nombres': nombres,
      'profesion': profesion,
      'colegio_profesional': colegioProfesional,
      'colegiatura': colegiatura,
      'correo': correo,
      'celular': celular,
      'rne': rne,
      'es_serums': esSerums,
      'periodo_serums': periodoSerums,
    };
  }

  String get nombreCompleto => '$nombres $apellidoPaterno ${apellidoMaterno ?? ''}'.trim();
}

/// Catálogos fijos usados por el formulario RR.HH. de Laravel
/// (RecursosHumanosController::getServiciosDisponibles/getProfesionesDisponibles).
class CatalogosRRHH {
  static const servicios = [
    'MEDICINA', 'ODONTOLOGÍA', 'ENFERMERÍA', 'OBSTETRICIA', 'PSICOLOGÍA',
    'NUTRICIÓN', 'FARMACIA', 'LABORATORIO', 'TRIAJE', 'URGENCIAS Y EMERGENCIAS',
    'TÓPICO', 'CRED', 'INMUNIZACIONES', 'ADMISIÓN Y ARCHIVO',
    'GESTIÓN ADMINISTRATIVA', 'OTROS',
  ];

  static const profesiones = [
    'MÉDICO CIRUJANO', 'CIRUJANO DENTISTA/ODONTÓLOGO(A)', 'LIC. EN ENFERMERÍA',
    'LIC. EN OBSTETRICIA', 'LIC. EN PSICOLOGÍA', 'LIC. EN NUTRICIÓN',
    'QUÍMICO FARMACÉUTICO(A)', 'LIC. TECNOLOGÍA MÉDICA',
    'TÉCNICO(A) EN ENFERMERÍA', 'TÉCNICO(A) EN FARMACIA',
    'TÉCNICO(A) EN LABORATORIO', 'PERSONAL ADMINISTRATIVO', 'OTROS',
  ];

  static const tiposDoc = ['DNI', 'C.E.'];
}
