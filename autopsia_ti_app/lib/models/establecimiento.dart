class Establecimiento {
  final int id;
  final String codigo;
  final String nombre;
  final String departamento;
  final String provincia;
  final String distrito;
  final String categoria;
  final String direccion;

  Establecimiento({
    required this.id,
    required this.codigo,
    required this.nombre,
    required this.departamento,
    required this.provincia,
    required this.distrito,
    required this.categoria,
    required this.direccion,
  });

  factory Establecimiento.fromJson(Map<String, dynamic> json) {
    return Establecimiento(
      id: json['id'] ?? 0,
      codigo: json['codigo'] ?? '',
      nombre: json['nombre'] ?? '',
      departamento: json['departamento'] ?? '',
      provincia: json['provincia'] ?? '',
      distrito: json['distrito'] ?? '',
      categoria: json['categoria'] ?? '',
      direccion: json['direccion'] ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'codigo': codigo,
      'nombre': nombre,
      'departamento': departamento,
      'provincia': provincia,
      'distrito': distrito,
      'categoria': categoria,
      'direccion': direccion,
    };
  }

  factory Establecimiento.fromMap(Map<String, dynamic> map) {
    return Establecimiento(
      id: map['id'],
      codigo: map['codigo'] ?? '',
      nombre: map['nombre'] ?? '',
      departamento: map['departamento'] ?? '',
      provincia: map['provincia'] ?? '',
      distrito: map['distrito'] ?? '',
      categoria: map['categoria'] ?? '',
      direccion: map['direccion'] ?? '',
    );
  }
}
