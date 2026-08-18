/// Espejo de la tabla `establecimientos` de Laravel (ver
/// storage/app/database_schema.json, generado por `php artisan schema:export-json`).
/// Los nombres de campo son exactamente los del $fillable de app/Models/Establecimiento.php.
class Establecimiento {
  final int? id;
  final String codigo;
  final String nombre;
  final String? institucion;
  final String? direccion;
  final String? departamento;
  final String provincia;
  final String distrito;
  final String? centroPoblado;
  final String? telefono;
  final String? correo;
  final String red;
  final String microred;
  final String? clas;
  final String? odsis;
  final String responsable;
  final String? tipoDocumento;
  final String? numeroDocumento;
  final String? colegioProfesional;
  final String? colegiatura;
  final String? rne;
  final String categoria;
  final String? estado;
  final String? condicion;
  final double? latitud;
  final double? longitud;
  final String? altitud;
  final String? fechaCreacionResolucion;
  final String? fechaRegistro;
  final String? numeroResolucionCreacion;
  final String? horarioAtencion;
  final String? numeroAmbientes;
  final String? numeroCamas;
  final String? upss; // JSON (texto) tal como lo guarda Laravel
  final String? ups; // JSON (texto) tal como lo guarda Laravel

  Establecimiento({
    this.id,
    required this.codigo,
    required this.nombre,
    this.institucion,
    this.direccion,
    this.departamento,
    required this.provincia,
    required this.distrito,
    this.centroPoblado,
    this.telefono,
    this.correo,
    required this.red,
    required this.microred,
    this.clas,
    this.odsis,
    required this.responsable,
    this.tipoDocumento,
    this.numeroDocumento,
    this.colegioProfesional,
    this.colegiatura,
    this.rne,
    required this.categoria,
    this.estado,
    this.condicion,
    this.latitud,
    this.longitud,
    this.altitud,
    this.fechaCreacionResolucion,
    this.fechaRegistro,
    this.numeroResolucionCreacion,
    this.horarioAtencion,
    this.numeroAmbientes,
    this.numeroCamas,
    this.upss,
    this.ups,
  });

  factory Establecimiento.fromJson(Map<String, dynamic> json) {
    return Establecimiento(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}'),
      codigo: json['codigo'] ?? '',
      nombre: json['nombre'] ?? '',
      institucion: json['institucion'],
      direccion: json['direccion'],
      departamento: json['departamento'],
      provincia: json['provincia'] ?? '',
      distrito: json['distrito'] ?? '',
      centroPoblado: json['centro_poblado'],
      telefono: json['telefono'],
      correo: json['correo'],
      red: json['red'] ?? '',
      microred: json['microred'] ?? '',
      clas: json['clas'],
      odsis: json['odsis'],
      responsable: json['responsable'] ?? '',
      tipoDocumento: json['tipo_documento'],
      numeroDocumento: json['numero_documento'],
      colegioProfesional: json['colegio_profesional'],
      colegiatura: json['colegiatura'],
      rne: json['rne'],
      categoria: json['categoria'] ?? '',
      estado: json['estado'],
      condicion: json['condicion'],
      latitud: json['latitud'] == null ? null : double.tryParse('${json['latitud']}'),
      longitud: json['longitud'] == null ? null : double.tryParse('${json['longitud']}'),
      altitud: json['altitud'],
      fechaCreacionResolucion: json['fecha_creacion_resolucion'],
      fechaRegistro: json['fecha_registro'],
      numeroResolucionCreacion: json['numero_resolucion_creacion'],
      horarioAtencion: json['horario_atencion'],
      numeroAmbientes: json['numero_ambientes'],
      numeroCamas: json['numero_camas'],
      upss: json['upss'] is String ? json['upss'] : json['upss']?.toString(),
      ups: json['ups'] is String ? json['ups'] : json['ups']?.toString(),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      if (id != null) 'id': id,
      'codigo': codigo,
      'nombre': nombre,
      'institucion': institucion,
      'direccion': direccion,
      'departamento': departamento,
      'provincia': provincia,
      'distrito': distrito,
      'centro_poblado': centroPoblado,
      'telefono': telefono,
      'correo': correo,
      'red': red,
      'microred': microred,
      'clas': clas,
      'odsis': odsis,
      'responsable': responsable,
      'tipo_documento': tipoDocumento,
      'numero_documento': numeroDocumento,
      'colegio_profesional': colegioProfesional,
      'colegiatura': colegiatura,
      'rne': rne,
      'categoria': categoria,
      'estado': estado,
      'condicion': condicion,
      'latitud': latitud,
      'longitud': longitud,
      'altitud': altitud,
      'fecha_creacion_resolucion': fechaCreacionResolucion,
      'fecha_registro': fechaRegistro,
      'numero_resolucion_creacion': numeroResolucionCreacion,
      'horario_atencion': horarioAtencion,
      'numero_ambientes': numeroAmbientes,
      'numero_camas': numeroCamas,
      'upss': upss,
      'ups': ups,
    };
  }

  factory Establecimiento.fromMap(Map<String, dynamic> map) => Establecimiento.fromJson(map);
}
