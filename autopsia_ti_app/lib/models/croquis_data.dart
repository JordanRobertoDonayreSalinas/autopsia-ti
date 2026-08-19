import 'dart:convert';

/// Espejo EXACTO del JSON que guarda Infraestructura2DController::store() en
/// mon_monitoreo_modulos.contenido (modulo_nombre='infraestructura_2d').
/// Claves de nivel superior en español ('elementos','conexiones'), claves
/// internas de cada elemento en inglés/abreviado — así las guarda el JS del
/// editor real, no se traducen para mantener compatibilidad de sincronización.
class CroquisData {
  List<CroquisElemento> elementos;
  List<CroquisConexion> conexiones;
  int totalPisos;
  double mapOffsetX;
  double mapOffsetY;
  double? mapAnchorX;
  double? mapAnchorY;

  CroquisData({
    List<CroquisElemento>? elementos,
    List<CroquisConexion>? conexiones,
    this.totalPisos = 1,
    this.mapOffsetX = 0,
    this.mapOffsetY = 0,
    this.mapAnchorX,
    this.mapAnchorY,
  })  : elementos = elementos ?? [],
        conexiones = conexiones ?? [];

  factory CroquisData.fromJson(Map<String, dynamic> json) {
    return CroquisData(
      elementos: (json['elementos'] as List? ?? []).map((e) => CroquisElemento.fromJson(Map<String, dynamic>.from(e))).toList(),
      conexiones: (json['conexiones'] as List? ?? []).map((e) => CroquisConexion.fromJson(Map<String, dynamic>.from(e))).toList(),
      totalPisos: json['totalPisos'] ?? 1,
      mapOffsetX: (json['mapOffsetX'] ?? 0).toDouble(),
      mapOffsetY: (json['mapOffsetY'] ?? 0).toDouble(),
      mapAnchorX: json['mapAnchorX'] == null ? null : (json['mapAnchorX'] as num).toDouble(),
      mapAnchorY: json['mapAnchorY'] == null ? null : (json['mapAnchorY'] as num).toDouble(),
    );
  }

  Map<String, dynamic> toJson() => {
        'elementos': elementos.map((e) => e.toJson()).toList(),
        'conexiones': conexiones.map((c) => c.toJson()).toList(),
        'totalPisos': totalPisos,
        'mapOffsetX': mapOffsetX,
        'mapOffsetY': mapOffsetY,
        'mapAnchorX': mapAnchorX,
        'mapAnchorY': mapAnchorY,
      };

  factory CroquisData.fromContenidoJson(String? raw) {
    if (raw == null || raw.isEmpty) return CroquisData();
    try {
      return CroquisData.fromJson(json.decode(raw));
    } catch (_) {
      return CroquisData();
    }
  }

  String toContenidoJson() => json.encode(toJson());
}

class CroquisElemento {
  String id;
  String type; // ambiente | hardware | puerta | calle | sistema | pasillo(legado)
  String? subtype;
  String? parentId;
  int piso;
  double x, y, w, h;
  double rot;
  String name;
  Map<String, dynamic> attrs; // {wifi:bool, light:bool, red:int} — solo con sentido en 'ambiente'
  int ts;
  String? estado; // OPERATIVO | REGULAR | INOPERATIVO — solo hardware
  int? cantidad;
  bool synced; // true si lo creó "Sincronizar módulos" (espejo de _synced)
  String? slug; // módulo/consultorio de origen cuando synced=true (espejo de _slug)

  CroquisElemento({
    required this.id,
    required this.type,
    this.subtype,
    this.parentId,
    this.piso = 1,
    required this.x,
    required this.y,
    required this.w,
    required this.h,
    this.rot = 0,
    this.name = '',
    Map<String, dynamic>? attrs,
    int? ts,
    this.estado,
    this.cantidad,
    this.synced = false,
    this.slug,
  })  : attrs = attrs ?? {'wifi': false, 'light': false, 'red': 0},
        ts = ts ?? DateTime.now().millisecondsSinceEpoch;

  factory CroquisElemento.fromJson(Map<String, dynamic> json) {
    return CroquisElemento(
      id: json['id'].toString(),
      type: json['type'] ?? 'ambiente',
      subtype: json['subtype'],
      parentId: json['parentId']?.toString(),
      piso: json['piso'] ?? 1,
      x: (json['x'] ?? 0).toDouble(),
      y: (json['y'] ?? 0).toDouble(),
      w: (json['w'] ?? 100).toDouble(),
      h: (json['h'] ?? 100).toDouble(),
      rot: (json['rot'] ?? 0).toDouble(),
      name: json['name'] ?? '',
      attrs: json['attrs'] != null ? Map<String, dynamic>.from(json['attrs']) : {'wifi': false, 'light': false, 'red': 0},
      ts: json['_ts'] ?? DateTime.now().millisecondsSinceEpoch,
      estado: json['estado'],
      cantidad: json['cantidad'],
      synced: json['_synced'] == true,
      slug: json['_slug'],
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type,
        'subtype': subtype,
        'parentId': parentId,
        'piso': piso,
        'x': x,
        'y': y,
        'w': w,
        'h': h,
        'rot': rot,
        'name': name,
        'attrs': attrs,
        '_ts': ts,
        if (estado != null) 'estado': estado,
        if (cantidad != null) 'cantidad': cantidad,
        if (synced) '_synced': true,
        if (slug != null) '_slug': slug,
      };
}

class CroquisConexion {
  String from;
  String to;

  CroquisConexion({required this.from, required this.to});

  factory CroquisConexion.fromJson(Map<String, dynamic> json) => CroquisConexion(from: json['from'].toString(), to: json['to'].toString());

  Map<String, dynamic> toJson() => {'from': from, 'to': to};
}
