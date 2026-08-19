import 'dart:convert';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../../models/croquis_catalogo.dart';
import '../../models/croquis_data.dart';
import '../../models/equipo_computo.dart';
import '../../models/monitoreo_modulo.dart';
import '../../repositories/acta_repository.dart';

/// Editor de croquis eléctrico — Fases 1 a 5 (completo): modelo de datos
/// fiel al JSON real de Infraestructura2DController
/// (elementos/conexiones/totalPisos), con agregar/seleccionar/mover/
/// eliminar/rotar (90° o libre por arrastre), redimensionar (8 manijas
/// rotation-aware), herramienta de conexión/cableado, zoom/pan (modo mano +
/// encuadrar todo), undo/redo, "Sincronizar módulos" (auto-poblado de
/// ambientes/equipos desde los consultorios e inventario ya capturados en
/// esta acta), multi-piso, y guardado offline. Deliberadamente NO incluye
/// colaboración en tiempo real (no viable offline) ni exportación de imagen
/// PNG para el PDF (el PDF de Laravel sigue funcionando igual sin ella,
/// solo pierde la miniatura embebida).
class CroquisEditorScreen extends StatefulWidget {
  final String actaOfflineId;
  final String establecimientoNombre;

  const CroquisEditorScreen({super.key, required this.actaOfflineId, required this.establecimientoNombre});

  @override
  State<CroquisEditorScreen> createState() => _CroquisEditorScreenState();
}

const double _grid = 10;
const double _canvasW = 1600;
const double _canvasH = 1100;
const double _minTamano = 20;

/// Signo (sx,sy) de cada manija de redimensionado respecto al centro, en el
/// espacio local sin rotar: -1/0/1 por eje. El punto opuesto (-sx,-sy) es el
/// que queda fijo mientras se arrastra la manija.
const _handleSigns = <String, Offset>{
  'nw': Offset(-1, -1), 'n': Offset(0, -1), 'ne': Offset(1, -1),
  'e': Offset(1, 0), 'se': Offset(1, 1), 's': Offset(0, 1),
  'sw': Offset(-1, 1), 'w': Offset(-1, 0),
};

Offset _rotarVector(Offset v, double radianes) {
  final cosA = math.cos(radianes), sinA = math.sin(radianes);
  return Offset(v.dx * cosA - v.dy * sinA, v.dx * sinA + v.dy * cosA);
}

class _CroquisEditorScreenState extends State<CroquisEditorScreen> {
  final _repo = ActaRepository();
  CroquisData _data = CroquisData();
  bool _cargando = true;
  bool _guardando = false;

  int _pisoActual = 1;
  String? _tipoActivo; // 'ambiente' | 'hardware' | 'puerta' | 'calle' | 'sistema'
  String? _subtipoActivo;
  String? _seleccionadoId;

  CroquisElemento? _arrastrando;
  Offset? _arrastreOffset;

  // Redimensionado con manijas (rotation-aware)
  CroquisElemento? _resizeElemento;
  String? _resizeHandle;
  double _resizeOldW = 0, _resizeOldH = 0;
  Offset _resizeOldCenter = Offset.zero;
  double _resizeRad = 0;

  // Rotación libre por arrastre de la manija circular
  CroquisElemento? _rotandoElemento;

  // Herramienta "Conectar" (cableado) — espejo de tool==='red' en el editor real.
  bool _modoConexion = false;
  String? _conexionOrigenId;
  Offset? _conexionPreviewEnd;

  // Zoom/pan — "modo mano" espejo de panMode del editor real. Con modo mano
  // apagado, el arrastre manipula elementos (no la cámara); encendido, el
  // arrastre panea el lienzo (vía InteractiveViewer). El mismo
  // TransformationController se reutiliza en ambos modos para que el
  // encuadre no se pierda al alternar.
  bool _modoMano = false;
  final _transformController = TransformationController();
  final _canvasContainerKey = GlobalKey();

  // Undo/redo — snapshot del JSON completo antes de cada acción mutante
  // (un snapshot por gesto, no por pixel), igual que el editor real.
  final List<String> _historial = [];
  final List<String> _futuro = [];
  static const _maxHistorial = 50;

  void _snapshot() {
    _historial.add(_data.toContenidoJson());
    if (_historial.length > _maxHistorial) _historial.removeAt(0);
    _futuro.clear();
  }

  void _undo() {
    if (_historial.isEmpty) return;
    _futuro.add(_data.toContenidoJson());
    final anterior = _historial.removeLast();
    setState(() {
      _data = CroquisData.fromContenidoJson(anterior);
      _seleccionadoId = null;
    });
  }

  void _redo() {
    if (_futuro.isEmpty) return;
    _historial.add(_data.toContenidoJson());
    final siguiente = _futuro.removeLast();
    setState(() {
      _data = CroquisData.fromContenidoJson(siguiente);
      _seleccionadoId = null;
    });
  }

  void _toggleModoMano() => setState(() => _modoMano = !_modoMano);

  void _zoom(double factor) {
    final m = _transformController.value.clone()..scaleByDouble(factor, factor, factor, 1);
    _transformController.value = m;
  }

  /// Espejo de autoFit(): centra y escala para encuadrar todos los
  /// elementos del piso actual, con margen y tope de zoom 2.0.
  void _encuadrarTodo() {
    final elementos = _elementosPiso;
    if (elementos.isEmpty) {
      _transformController.value = Matrix4.identity();
      return;
    }
    final viewport = _canvasContainerKey.currentContext?.size ?? const Size(_canvasW, _canvasH);

    double minX = double.infinity, minY = double.infinity, maxX = -double.infinity, maxY = -double.infinity;
    for (final e in elementos) {
      minX = math.min(minX, e.x);
      minY = math.min(minY, e.y);
      maxX = math.max(maxX, e.x + e.w);
      maxY = math.max(maxY, e.y + e.h);
    }
    const margen = 60;
    minX -= margen; minY -= margen; maxX += margen; maxY += margen;
    final bboxW = (maxX - minX).clamp(1, double.infinity);
    final bboxH = (maxY - minY).clamp(1, double.infinity);
    final bboxCenter = Offset((minX + maxX) / 2, (minY + maxY) / 2);

    var scale = math.min(viewport.width / bboxW, viewport.height / bboxH);
    scale = scale.clamp(0.1, 2.0);

    final viewportCenter = Offset(viewport.width / 2, viewport.height / 2);
    final translate = viewportCenter - bboxCenter * scale;

    setState(() {
      _transformController.value = Matrix4.identity()
        ..translateByDouble(translate.dx, translate.dy, 0, 1)
        ..scaleByDouble(scale, scale, scale, 1);
    });
  }

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  Future<void> _cargar() async {
    final modulo = await _repo.obtenerModuloInfraestructura2D(widget.actaOfflineId);
    if (!mounted) return;
    setState(() {
      _data = CroquisData.fromContenidoJson(modulo?.contenido);
      _cargando = false;
    });
  }

  Future<void> _guardar() async {
    setState(() => _guardando = true);
    await _repo.guardarModuloInfraestructura2D(widget.actaOfflineId, _data.toContenidoJson());
    if (!mounted) return;
    setState(() => _guardando = false);
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(backgroundColor: Color(0xFF15803D), content: Text('Croquis guardado en disco local.')),
    );
  }

  double _snap(double v) => (v / _grid).round() * _grid;

  List<CroquisElemento> get _elementosPiso => _data.elementos.where((e) => e.piso == _pisoActual).toList();

  CroquisElemento? _elementoEnPunto(Offset p) {
    for (final e in _elementosPiso.reversed) {
      if (e.parentId != null) continue; // los hijos no se seleccionan sueltos
      if (p.dx >= e.x && p.dx <= e.x + e.w && p.dy >= e.y && p.dy <= e.y + e.h) return e;
    }
    return null;
  }

  /// Devuelve 'rotate', una clave de _handleSigns, o null si `p` no cae
  /// sobre ninguna manija del elemento seleccionado.
  String? _handleEnPunto(CroquisElemento el, Offset p) {
    final center = Offset(el.x + el.w / 2, el.y + el.h / 2);
    final rad = el.rot * math.pi / 180;

    final rotHandleWorld = center + _rotarVector(Offset(0, -el.h / 2 - 24), rad);
    if ((p - rotHandleWorld).distance <= 13) return 'rotate';

    for (final entry in _handleSigns.entries) {
      final local = Offset(entry.value.dx * el.w / 2, entry.value.dy * el.h / 2);
      final world = center + _rotarVector(local, rad);
      if ((p - world).distance <= 11) return entry.key;
    }
    return null;
  }

  void _iniciarResize(CroquisElemento el, String handle) {
    _snapshot();
    _resizeElemento = el;
    _resizeHandle = handle;
    _resizeOldW = el.w;
    _resizeOldH = el.h;
    _resizeOldCenter = Offset(el.x + el.w / 2, el.y + el.h / 2);
    _resizeRad = el.rot * math.pi / 180;
  }

  void _actualizarResize(Offset punteroMundo) {
    final el = _resizeElemento;
    final handle = _resizeHandle;
    if (el == null || handle == null) return;
    final sign = _handleSigns[handle]!;

    final f = Offset(-sign.dx * _resizeOldW / 2, -sign.dy * _resizeOldH / 2);
    final q = _rotarVector(punteroMundo - _resizeOldCenter, -_resizeRad);

    double newW = _resizeOldW, newH = _resizeOldH;
    if (sign.dx != 0) newW = _snap(math.max(_minTamano, sign.dx * (q.dx - f.dx)));
    if (sign.dy != 0) newH = _snap(math.max(_minTamano, sign.dy * (q.dy - f.dy)));

    final newCenterLocal = Offset(sign.dx * (newW - _resizeOldW) / 2, sign.dy * (newH - _resizeOldH) / 2);
    final newCenterWorld = _resizeOldCenter + _rotarVector(newCenterLocal, _resizeRad);

    setState(() {
      el.w = newW;
      el.h = newH;
      el.x = newCenterWorld.dx - newW / 2;
      el.y = newCenterWorld.dy - newH / 2;
    });
  }

  void _actualizarRotacion(CroquisElemento el, Offset punteroMundo) {
    final center = Offset(el.x + el.w / 2, el.y + el.h / 2);
    final v = punteroMundo - center;
    final anguloGrados = math.atan2(v.dy, v.dx) * 180 / math.pi;
    var grados = (anguloGrados + 90) % 360;
    if (grados < 0) grados += 360;
    setState(() => _aplicarRotacion(el, grados));
  }

  /// Al rotar un ambiente, sus hijos giran alrededor del centro del padre
  /// y también rotan sobre sí mismos el mismo delta (ver informe de
  /// auditoría del editor real).
  void _aplicarRotacion(CroquisElemento el, double nuevoRot) {
    var delta = nuevoRot - el.rot;
    // Evita saltos grandes al cruzar el límite 0°/360° durante un arrastre continuo.
    if (delta > 180) delta -= 360;
    if (delta < -180) delta += 360;

    el.rot = nuevoRot;

    if (el.type == 'ambiente' && delta != 0) {
      final center = Offset(el.x + el.w / 2, el.y + el.h / 2);
      final rad = delta * math.pi / 180;
      for (final hijoId in _hijosDe(el.id)) {
        final hijo = _data.elementos.firstWhere((e) => e.id == hijoId);
        final hijoCentro = Offset(hijo.x + hijo.w / 2, hijo.y + hijo.h / 2);
        final nuevoCentro = center + _rotarVector(hijoCentro - center, rad);
        hijo.x = nuevoCentro.dx - hijo.w / 2;
        hijo.y = nuevoCentro.dy - hijo.h / 2;
        hijo.rot = (hijo.rot + delta) % 360;
        if (hijo.rot < 0) hijo.rot += 360;
      }
    }
  }

  void _colocarElemento(Offset p) {
    final tipo = _tipoActivo!;
    final subtipo = _subtipoActivo!;
    final info = CroquisCatalogo.infoDe(tipo, subtipo);
    final x = _snap(p.dx - info.defaultW / 2);
    final y = _snap(p.dy - info.defaultH / 2);

    String? parentId;
    if (tipo == 'hardware' || tipo == 'sistema') {
      for (final e in _elementosPiso) {
        if (e.type == 'ambiente' && x >= e.x && y >= e.y && x + info.defaultW <= e.x + e.w && y + info.defaultH <= e.y + e.h) {
          parentId = e.id;
          break;
        }
      }
    }

    final nuevo = CroquisElemento(
      id: 'el_${DateTime.now().microsecondsSinceEpoch}',
      type: tipo,
      subtype: subtipo,
      parentId: parentId,
      piso: _pisoActual,
      x: x,
      y: y,
      w: info.defaultW,
      h: info.defaultH,
      name: tipo == 'ambiente' ? info.label.toUpperCase() : '',
      estado: tipo == 'hardware' ? 'OPERATIVO' : null,
    );

    _snapshot();
    setState(() {
      _data.elementos.add(nuevo);
      _seleccionadoId = nuevo.id;
      _tipoActivo = null;
      _subtipoActivo = null;
    });
  }

  List<String> _hijosDe(String id) => _data.elementos.where((e) => e.parentId == id).map((e) => e.id).toList();

  void _eliminarSeleccionado() {
    final id = _seleccionadoId;
    if (id == null) return;
    _snapshot();
    final hijos = _hijosDe(id);
    final idsABorrar = {id, ...hijos};
    setState(() {
      _data.elementos.removeWhere((e) => idsABorrar.contains(e.id));
      _data.conexiones.removeWhere((c) => idsABorrar.contains(c.from) || idsABorrar.contains(c.to));
      _seleccionadoId = null;
    });
  }

  void _rotarSeleccionado() {
    final id = _seleccionadoId;
    if (id == null) return;
    _snapshot();
    final el = _data.elementos.firstWhere((e) => e.id == id);
    setState(() => _aplicarRotacion(el, (el.rot + 90) % 360));
  }

  void _moverElemento(CroquisElemento el, Offset delta) {
    final dx = delta.dx;
    final dy = delta.dy;
    el.x = _snap(el.x + dx);
    el.y = _snap(el.y + dy);
    if (el.type == 'ambiente') {
      for (final hijoId in _hijosDe(el.id)) {
        final hijo = _data.elementos.firstWhere((e) => e.id == hijoId);
        hijo.x = _snap(hijo.x + dx);
        hijo.y = _snap(hijo.y + dy);
      }
    }
  }

  void _toggleModoConexion() {
    setState(() {
      _modoConexion = !_modoConexion;
      _tipoActivo = null;
      _subtipoActivo = null;
      _conexionOrigenId = null;
      _conexionPreviewEnd = null;
    });
  }

  bool _yaConectados(String a, String b) =>
      _data.conexiones.any((c) => (c.from == a && c.to == b) || (c.from == b && c.to == a));

  void _finalizarConexion(Offset p) {
    final origenId = _conexionOrigenId;
    setState(() {
      _conexionOrigenId = null;
      _conexionPreviewEnd = null;
    });
    if (origenId == null) return;
    final destino = _elementoEnPunto(p);
    if (destino == null || destino.id == origenId || _yaConectados(origenId, destino.id)) return;
    _snapshot();
    setState(() => _data.conexiones.add(CroquisConexion(from: origenId, to: destino.id)));
  }

  String _tituloDeConsultorio(MonitoreoModulo m) {
    try {
      final data = jsonDecode(m.contenido) as Map<String, dynamic>;
      final t = (data['titulo_consultorio'] as String?)?.trim();
      return t != null && t.isNotEmpty ? t : m.moduloNombre;
    } catch (_) {
      return m.moduloNombre;
    }
  }

  /// Heurística simple sobre el texto libre de EquipoComputo.descripcion —
  /// no hay un campo de tipo estructurado en el inventario real.
  String _subtipoHardwareDesde(String descripcion) {
    final d = descripcion.toUpperCase();
    if (d.contains('LAPTOP')) return 'laptop';
    if (d.contains('TABLET')) return 'tablet';
    if (d.contains('MONITOR')) return 'monitor';
    if (d.contains('TECLADO')) return 'teclado';
    if (d.contains('MOUSE') || d.contains('RATON')) return 'mouse';
    if (d.contains('IMPRESORA')) return 'impresora';
    if (d.contains('TICKETERA')) return 'ticketera';
    if (d.contains('LECTOR') || d.contains('ESCANER') || d.contains('SCANNER')) return 'escaner';
    if (d.contains('ROUTER')) return 'router';
    if (d.contains('ACCESS POINT') || d.contains(' AP ')) return 'ap';
    if (d.contains('SWITCH')) return 'switch';
    if (d.contains('UPS')) return 'ups';
    return 'pc';
  }

  /// Espejo de "Sincronizar módulos" (prepopularModulos) del editor real:
  /// reconstruye ambientes por cada consultorio dinámico ya capturado en
  /// esta acta, con sus equipos de cómputo adentro, más salas especiales
  /// para Pozo a Tierra / Panel Solar si aplica. Los elementos generados
  /// quedan marcados `synced` para poder regenerarlos sin duplicar ni tocar
  /// lo que el auditor agregó manualmente en el lienzo.
  Future<void> _sincronizarModulos() async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Sincronizar módulos'),
        content: const Text(
          'Se regeneran los ambientes y equipos auto-generados a partir de los consultorios y el inventario ya capturados en esta acta. '
          'Lo que agregaste manualmente en el lienzo no se toca.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Sincronizar'),
          ),
        ],
      ),
    );
    if (confirmar != true) return;

    final consultorios = await _repo.obtenerConsultoriosDinamicos(widget.actaOfflineId);
    final equipos = await _repo.obtenerEquipos(widget.actaOfflineId);
    final acta = await _repo.obtenerPorOfflineId(widget.actaOfflineId);
    if (!mounted) return;

    _snapshot();

    setState(() {
      final idsViejos = _data.elementos.where((e) => e.synced).map((e) => e.id).toSet();
      _data.elementos.removeWhere((e) => e.synced);
      _data.conexiones.removeWhere((c) => idsViejos.contains(c.from) || idsViejos.contains(c.to));

      const colWidth = 200.0, rowHeight = 170.0, cols = 6;
      var index = 0;

      void agregarSala(String id, String slug, String nombre, List<EquipoComputo> equiposDeSala) {
        final col = index % cols, row = index ~/ cols;
        final x = 60.0 + col * colWidth, y = 60.0 + row * rowHeight;
        _data.elementos.add(CroquisElemento(
          id: id, type: 'ambiente', subtype: 'consultorio_funcional', piso: 1,
          x: x, y: y, w: 160, h: 120, name: nombre.toUpperCase(), synced: true, slug: slug,
        ));
        for (var i = 0; i < equiposDeSala.length; i++) {
          final eq = equiposDeSala[i];
          _data.elementos.add(CroquisElemento(
            id: '${id}_hw_$i',
            type: 'hardware',
            subtype: _subtipoHardwareDesde(eq.descripcion),
            parentId: id,
            piso: 1,
            x: x + 15 + (i % 2) * 62,
            y: y + 35 + (i ~/ 2) * 56,
            w: 50,
            h: 46,
            name: eq.descripcion,
            estado: eq.estado.toUpperCase(),
            cantidad: eq.cantidad,
            synced: true,
            slug: slug,
          ));
        }
        index++;
      }

      for (final consultorio in consultorios) {
        final equiposDelModulo = equipos.where((e) => e.modulo == consultorio.moduloNombre).toList();
        agregarSala('sync_${consultorio.moduloNombre}', consultorio.moduloNombre, _tituloDeConsultorio(consultorio), equiposDelModulo);
      }

      if (acta?.pozoTierra == 'SI') {
        agregarSala('sync_pozo_tierra', 'pozo_tierra', 'Pozo a Tierra', [
          EquipoComputo(actaOfflineId: widget.actaOfflineId, modulo: 'pozo_tierra', descripcion: 'POZO TIERRA', cantidad: acta?.pozoTierraCantidad ?? 1, estado: 'Operativo'),
        ]);
      }
      if (acta?.panelSolar == 'SI') {
        agregarSala('sync_panel_solar', 'panel_solar', 'Panel Solar', [
          EquipoComputo(actaOfflineId: widget.actaOfflineId, modulo: 'panel_solar', descripcion: 'PANEL SOLAR', cantidad: acta?.panelSolarCantidad ?? 1, estado: 'Operativo'),
        ]);
      }
    });

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      backgroundColor: const Color(0xFF15803D),
      content: Text('Módulos sincronizados: ${consultorios.length} consultorio(s), ${equipos.length} equipo(s).'),
    ));
  }

  void _agregarPiso() {
    _snapshot();
    setState(() {
      _data.totalPisos++;
      _pisoActual = _data.totalPisos;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final seleccionado = _seleccionadoId == null ? null : _data.elementos.where((e) => e.id == _seleccionadoId).firstOrNull;

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: Text('Croquis · ${widget.establecimientoNombre}', overflow: TextOverflow.ellipsis),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            onPressed: _historial.isEmpty ? null : _undo,
            icon: const Icon(Icons.undo_rounded),
            tooltip: 'Deshacer',
          ),
          IconButton(
            onPressed: _futuro.isEmpty ? null : _redo,
            icon: const Icon(Icons.redo_rounded),
            tooltip: 'Rehacer',
          ),
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Center(
              child: ElevatedButton.icon(
                onPressed: _guardando ? null : _guardar,
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
                icon: _guardando
                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.save_rounded, size: 16),
                label: const Text('Guardar'),
              ),
            ),
          ),
        ],
      ),
      body: Row(
        children: [
          _panelHerramientas(),
          Expanded(
            child: Column(
              children: [
                _barraPisos(),
                Expanded(
                  child: Container(
                    key: _canvasContainerKey,
                    color: const Color(0xFFE2E8F0),
                    child: ClipRect(child: _modoMano ? _lienzoConModoMano() : _lienzoEditable()),
                  ),
                ),
              ],
            ),
          ),
          if (seleccionado != null) _panelPropiedades(seleccionado),
        ],
      ),
    );
  }

  /// Modo mano activo: InteractiveViewer maneja pan/zoom con el dedo/mouse;
  /// el lienzo no reacciona a toques (se sale del modo mano para editar).
  Widget _lienzoConModoMano() {
    return InteractiveViewer(
      transformationController: _transformController,
      minScale: 0.2,
      maxScale: 4,
      boundaryMargin: const EdgeInsets.all(2000),
      child: SizedBox(
        width: _canvasW,
        height: _canvasH,
        child: CustomPaint(
          painter: _CroquisPainter(
            elementos: _elementosPiso,
            conexiones: _data.conexiones,
            todos: _data.elementos,
            seleccionadoId: _seleccionadoId,
          ),
        ),
      ),
    );
  }

  /// Modo edición (normal): el mismo TransformationController se aplica como
  /// Transform estático (sin consumir gestos de arrastre), así el encuadre
  /// elegido en modo mano se conserva y el GestureDetector sigue recibiendo
  /// coordenadas en el espacio lógico del lienzo (Flutter invierte el
  /// Transform automáticamente al hacer hit-testing).
  Widget _lienzoEditable() {
    return ValueListenableBuilder<Matrix4>(
      valueListenable: _transformController,
      builder: (context, matrix, _) {
        return Transform(
          transform: matrix,
          child: SizedBox(
            width: _canvasW,
            height: _canvasH,
            child: GestureDetector(
              onTapUp: (details) {
                            final p = details.localPosition;
                            if (_modoConexion) {
                              final el = _elementoEnPunto(p);
                              if (_conexionOrigenId == null) {
                                if (el != null) setState(() => _conexionOrigenId = el.id);
                              } else {
                                _finalizarConexion(p);
                              }
                            } else if (_tipoActivo != null && _subtipoActivo != null) {
                              _colocarElemento(p);
                            } else {
                              setState(() => _seleccionadoId = _elementoEnPunto(p)?.id);
                            }
                          },
                          onPanStart: (details) {
                            final p = details.localPosition;
                            if (_modoConexion) {
                              final el = _elementoEnPunto(p);
                              if (el != null) setState(() => _conexionOrigenId = el.id);
                              return;
                            }

                            final seleccionadoActual = _seleccionadoId == null
                                ? null
                                : _elementosPiso.where((e) => e.id == _seleccionadoId).firstOrNull;

                            if (seleccionadoActual != null) {
                              final handle = _handleEnPunto(seleccionadoActual, p);
                              if (handle == 'rotate') {
                                _snapshot();
                                _rotandoElemento = seleccionadoActual;
                                return;
                              } else if (handle != null) {
                                _iniciarResize(seleccionadoActual, handle);
                                return;
                              }
                            }

                            final el = _elementoEnPunto(p);
                            if (el != null) {
                              _snapshot();
                              _arrastrando = el;
                              _arrastreOffset = p;
                              setState(() => _seleccionadoId = el.id);
                            }
                          },
                          onPanUpdate: (details) {
                            final p = details.localPosition;
                            if (_modoConexion) {
                              if (_conexionOrigenId != null) setState(() => _conexionPreviewEnd = p);
                              return;
                            }
                            if (_rotandoElemento != null) {
                              _actualizarRotacion(_rotandoElemento!, p);
                            } else if (_resizeElemento != null) {
                              _actualizarResize(p);
                            } else if (_arrastrando != null && _arrastreOffset != null) {
                              final delta = p - _arrastreOffset!;
                              setState(() => _moverElemento(_arrastrando!, delta));
                              _arrastreOffset = p;
                            }
                          },
                          onPanEnd: (_) {
                            if (_modoConexion && _conexionOrigenId != null && _conexionPreviewEnd != null) {
                              _finalizarConexion(_conexionPreviewEnd!);
                            }
                            _arrastrando = null;
                            _arrastreOffset = null;
                            _resizeElemento = null;
                            _resizeHandle = null;
                            _rotandoElemento = null;
                          },
              child: CustomPaint(
                size: const Size(_canvasW, _canvasH),
                painter: _CroquisPainter(
                  elementos: _elementosPiso,
                  conexiones: _data.conexiones,
                  todos: _data.elementos,
                  seleccionadoId: _seleccionadoId,
                  conexionOrigenId: _conexionOrigenId,
                  conexionPreviewEnd: _conexionPreviewEnd,
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _barraPisos() {
    return Container(
      height: 44,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Row(
        children: [
          const Text('PISO', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8))),
          const SizedBox(width: 10),
          ...List.generate(_data.totalPisos, (i) {
            final n = i + 1;
            final activo = n == _pisoActual;
            return Padding(
              padding: const EdgeInsets.only(right: 6),
              child: ChoiceChip(
                label: Text('$n'),
                selected: activo,
                onSelected: (_) => setState(() {
                  _pisoActual = n;
                  _seleccionadoId = null;
                }),
              ),
            );
          }),
          IconButton(onPressed: _agregarPiso, icon: const Icon(Icons.add_circle_outline_rounded, size: 20), tooltip: 'Agregar piso'),
          const VerticalDivider(width: 24, indent: 8, endIndent: 8),
          IconButton(
            onPressed: () => _zoom(1.2),
            icon: const Icon(Icons.zoom_in_rounded, size: 20),
            tooltip: 'Acercar',
          ),
          IconButton(
            onPressed: () => _zoom(1 / 1.2),
            icon: const Icon(Icons.zoom_out_rounded, size: 20),
            tooltip: 'Alejar',
          ),
          IconButton(
            onPressed: _encuadrarTodo,
            icon: const Icon(Icons.center_focus_strong_rounded, size: 20),
            tooltip: 'Encuadrar todo',
          ),
          IconButton(
            onPressed: _toggleModoMano,
            icon: Icon(Icons.pan_tool_rounded, size: 20, color: _modoMano ? const Color(0xFF4F46E5) : null),
            tooltip: _modoMano ? 'Modo mano activo (tocar para editar)' : 'Modo mano (pan/zoom)',
            style: _modoMano ? IconButton.styleFrom(backgroundColor: const Color(0xFFEEF2FF)) : null,
          ),
          if (_tipoActivo != null && _subtipoActivo != null) ...[
            const SizedBox(width: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(8)),
              child: const Text('Toque el plano para colocar el elemento', style: TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.w600)),
            ),
          ],
        ],
      ),
    );
  }

  Widget _panelHerramientas() {
    return Container(
      width: 220,
      color: Colors.white,
      child: ListView(
        padding: const EdgeInsets.all(12),
        children: [
          Material(
            color: _modoConexion ? const Color(0xFFECFDF5) : Colors.transparent,
            borderRadius: BorderRadius.circular(10),
            child: InkWell(
              borderRadius: BorderRadius.circular(10),
              onTap: _toggleModoConexion,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                child: Row(
                  children: [
                    Icon(Icons.cable_rounded, size: 18, color: _modoConexion ? const Color(0xFF059669) : const Color(0xFF334155)),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _modoConexion ? 'Conectando… (toque origen y destino)' : 'Conectar (Cableado)',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: _modoConexion ? const Color(0xFF059669) : const Color(0xFF334155)),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          Material(
            color: Colors.transparent,
            borderRadius: BorderRadius.circular(10),
            child: InkWell(
              borderRadius: BorderRadius.circular(10),
              onTap: _sincronizarModulos,
              child: const Padding(
                padding: EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                child: Row(
                  children: [
                    Icon(Icons.sync_rounded, size: 18, color: Color(0xFF334155)),
                    SizedBox(width: 10),
                    Expanded(
                      child: Text('Sincronizar Módulos', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF334155))),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const Divider(height: 20),
          const Text('ELEMENTOS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.5)),
          const SizedBox(height: 8),
          ...CroquisCatalogo.porTipo.entries.map((entry) => _grupoHerramientas(entry.key, entry.value)),
        ],
      ),
    );
  }

  Widget _grupoHerramientas(String tipo, Map<String, CroquisTipoInfo> subtipos) {
    final titulos = {'ambiente': 'Ambientes', 'hardware': 'Hardware', 'puerta': 'Puertas / Accesos', 'calle': 'Calles', 'sistema': 'Sistemas'};
    return Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        tilePadding: EdgeInsets.zero,
        title: Text(titulos[tipo] ?? tipo, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
        childrenPadding: const EdgeInsets.only(bottom: 8),
        children: subtipos.entries.map((e) {
          final activo = _tipoActivo == tipo && _subtipoActivo == e.key;
          return Material(
            color: activo ? const Color(0xFFEEF2FF) : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
            child: InkWell(
              borderRadius: BorderRadius.circular(8),
              onTap: () => setState(() {
                _tipoActivo = tipo;
                _subtipoActivo = e.key;
                _seleccionadoId = null;
                _modoConexion = false;
                _conexionOrigenId = null;
              }),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                child: Row(
                  children: [
                    Icon(e.value.icon, size: 16, color: e.value.ink),
                    const SizedBox(width: 8),
                    Expanded(child: Text(e.value.label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _panelPropiedades(CroquisElemento el) {
    final info = CroquisCatalogo.infoDe(el.type, el.subtype);
    return Container(
      width: 240,
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(info.icon, color: info.ink),
              const SizedBox(width: 8),
              Expanded(child: Text(info.label, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13))),
              IconButton(onPressed: () => setState(() => _seleccionadoId = null), icon: const Icon(Icons.close_rounded, size: 18)),
            ],
          ),
          const Divider(height: 24),
          TextFormField(
            key: ValueKey(el.id),
            initialValue: el.name,
            decoration: const InputDecoration(labelText: 'Nombre / Etiqueta', isDense: true),
            onChanged: (v) => setState(() => el.name = v),
          ),
          const SizedBox(height: 16),
          if (el.type == 'hardware') ...[
            DropdownButtonFormField<String>(
              initialValue: el.estado ?? 'OPERATIVO',
              decoration: const InputDecoration(labelText: 'Estado', isDense: true),
              items: const [
                DropdownMenuItem(value: 'OPERATIVO', child: Text('OPERATIVO')),
                DropdownMenuItem(value: 'REGULAR', child: Text('REGULAR')),
                DropdownMenuItem(value: 'INOPERATIVO', child: Text('INOPERATIVO')),
              ],
              onChanged: (v) => setState(() => el.estado = v),
            ),
            const SizedBox(height: 16),
          ],
          if (el.type == 'ambiente') ...[
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('WiFi', style: TextStyle(fontSize: 12)),
              value: el.attrs['wifi'] == true,
              onChanged: (v) => setState(() => el.attrs['wifi'] = v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('Iluminación', style: TextStyle(fontSize: 12)),
              value: el.attrs['light'] == true,
              onChanged: (v) => setState(() => el.attrs['light'] = v),
            ),
            Row(
              children: [
                const Expanded(child: Text('Puntos de red', style: TextStyle(fontSize: 12))),
                IconButton(icon: const Icon(Icons.remove_circle_outline, size: 18), onPressed: () => setState(() => el.attrs['red'] = ((el.attrs['red'] ?? 0) as int) > 0 ? el.attrs['red'] - 1 : 0)),
                Text('${el.attrs['red'] ?? 0}'),
                IconButton(icon: const Icon(Icons.add_circle_outline, size: 18), onPressed: () => setState(() => el.attrs['red'] = ((el.attrs['red'] ?? 0) as int) + 1)),
              ],
            ),
            const SizedBox(height: 16),
          ],
          OutlinedButton.icon(onPressed: _rotarSeleccionado, icon: const Icon(Icons.rotate_right_rounded, size: 16), label: const Text('Rotar 90°')),
          const SizedBox(height: 8),
          OutlinedButton.icon(
            onPressed: _eliminarSeleccionado,
            style: OutlinedButton.styleFrom(foregroundColor: const Color(0xFFB91C1C), side: const BorderSide(color: Color(0xFFFCA5A5))),
            icon: const Icon(Icons.delete_outline_rounded, size: 16),
            label: const Text('Eliminar'),
          ),
        ],
      ),
    );
  }
}

class _CroquisPainter extends CustomPainter {
  final List<CroquisElemento> elementos;
  final List<CroquisConexion> conexiones;
  final List<CroquisElemento> todos; // para resolver conexiones entre pisos
  final String? seleccionadoId;
  final String? conexionOrigenId;
  final Offset? conexionPreviewEnd;

  _CroquisPainter({
    required this.elementos,
    required this.conexiones,
    required this.todos,
    required this.seleccionadoId,
    this.conexionOrigenId,
    this.conexionPreviewEnd,
  });

  @override
  void paint(Canvas canvas, Size size) {
    _dibujarGrilla(canvas, size);

    // Conexiones (solo entre elementos visibles en el piso actual)
    final idsVisibles = elementos.map((e) => e.id).toSet();
    for (final c in conexiones) {
      if (!idsVisibles.contains(c.from) || !idsVisibles.contains(c.to)) continue;
      final el1 = elementos.where((e) => e.id == c.from).firstOrNull;
      final el2 = elementos.where((e) => e.id == c.to).firstOrNull;
      if (el1 == null || el2 == null) continue;
      _dibujarConexion(canvas, el1, el2);
    }

    for (final e in elementos) {
      _dibujarElemento(canvas, e, seleccionado: e.id == seleccionadoId, conectando: e.id == conexionOrigenId);
    }

    if (seleccionadoId != null) {
      final sel = elementos.where((e) => e.id == seleccionadoId).firstOrNull;
      if (sel != null) _dibujarManijas(canvas, sel);
    }

    // Cable en curso (herramienta Conectar)
    if (conexionOrigenId != null && conexionPreviewEnd != null) {
      final origen = elementos.where((e) => e.id == conexionOrigenId).firstOrNull;
      if (origen != null) {
        final a = _anchorEn(origen, conexionPreviewEnd!);
        final dashPaint = Paint()
          ..color = const Color(0xFF10B981)
          ..strokeWidth = 2
          ..style = PaintingStyle.stroke;
        _drawDashedLine(canvas, a, conexionPreviewEnd!, dashPaint);
        canvas.drawCircle(conexionPreviewEnd!, 4, Paint()..color = const Color(0xFF10B981));
      }
    }
  }

  void _drawDashedLine(Canvas canvas, Offset a, Offset b, Paint paint) {
    const dashWidth = 6.0, dashSpace = 4.0;
    final total = (b - a).distance;
    if (total == 0) return;
    final dir = (b - a) / total;
    double dist = 0;
    while (dist < total) {
      final start = a + dir * dist;
      final end = a + dir * math.min(dist + dashWidth, total);
      canvas.drawLine(start, end, paint);
      dist += dashWidth + dashSpace;
    }
  }

  void _dibujarManijas(Canvas canvas, CroquisElemento el) {
    final center = Offset(el.x + el.w / 2, el.y + el.h / 2);
    final rad = el.rot * math.pi / 180;

    // Línea + manija de rotación
    final rotHandle = center + _rotarVector(Offset(0, -el.h / 2 - 24), rad);
    final borde = center + _rotarVector(Offset(0, -el.h / 2), rad);
    canvas.drawLine(borde, rotHandle, Paint()..color = const Color(0xFF4F46E5)..strokeWidth = 1.5);
    canvas.drawCircle(rotHandle, 7, Paint()..color = Colors.white);
    canvas.drawCircle(rotHandle, 7, Paint()..color = const Color(0xFF4F46E5)..strokeWidth = 2..style = PaintingStyle.stroke);

    // Manijas de redimensionado
    for (final entry in _handleSigns.entries) {
      final local = Offset(entry.value.dx * el.w / 2, entry.value.dy * el.h / 2);
      final world = center + _rotarVector(local, rad);
      final rect = Rect.fromCenter(center: world, width: 9, height: 9);
      canvas.drawRect(rect, Paint()..color = Colors.white);
      canvas.drawRect(rect, Paint()..color = const Color(0xFF4F46E5)..strokeWidth = 1.5..style = PaintingStyle.stroke);
    }
  }

  void _dibujarGrilla(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFCBD5E1)
      ..strokeWidth = 1;
    canvas.drawRect(Rect.fromLTWH(0, 0, size.width, size.height), Paint()..color = Colors.white);
    for (double x = 0; x < size.width; x += 40) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint..color = const Color(0xFFF1F5F9));
    }
    for (double y = 0; y < size.height; y += 40) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint..color = const Color(0xFFF1F5F9));
    }
  }

  Offset _centro(CroquisElemento e) => Offset(e.x + e.w / 2, e.y + e.h / 2);

  Offset _anchorEn(CroquisElemento e, Offset hacia) {
    final c = _centro(e);
    final dx = hacia.dx - c.dx;
    final dy = hacia.dy - c.dy;
    final hw = e.w / 2 + 2, hh = e.h / 2 + 2;
    if (dx == 0 && dy == 0) return c;
    final scaleX = hw / dx.abs();
    final scaleY = hh / dy.abs();
    final scale = scaleX < scaleY ? scaleX : scaleY;
    return Offset(c.dx + dx * scale.clamp(0, 1), c.dy + dy * scale.clamp(0, 1));
  }

  void _dibujarConexion(Canvas canvas, CroquisElemento el1, CroquisElemento el2) {
    final c1 = _centro(el1);
    final c2 = _centro(el2);
    final a = _anchorEn(el1, c2);
    final b = _anchorEn(el2, c1);
    final codo = Offset(b.dx, a.dy);

    final path = Path()
      ..moveTo(a.dx, a.dy)
      ..lineTo(codo.dx, codo.dy)
      ..lineTo(b.dx, b.dy);

    canvas.drawPath(path, Paint()..color = Colors.white.withValues(alpha: 0.8)..strokeWidth = 4.5..style = PaintingStyle.stroke..strokeCap = StrokeCap.round);
    canvas.drawPath(path, Paint()..color = const Color(0xFF60A5FA)..strokeWidth = 1.8..style = PaintingStyle.stroke..strokeCap = StrokeCap.round);
    canvas.drawCircle(a, 2.6, Paint()..color = const Color(0xFF2563EB));
    canvas.drawCircle(b, 2.6, Paint()..color = const Color(0xFF2563EB));
  }

  void _dibujarElemento(Canvas canvas, CroquisElemento e, {required bool seleccionado, bool conectando = false}) {
    final info = CroquisCatalogo.infoDe(e.type, e.subtype);
    canvas.save();
    canvas.translate(e.x + e.w / 2, e.y + e.h / 2);
    canvas.rotate(e.rot * 3.14159265 / 180);
    final rect = Rect.fromCenter(center: Offset.zero, width: e.w, height: e.h);
    final rrect = RRect.fromRectAndRadius(rect, const Radius.circular(8));

    final estadoColor = e.estado != null ? CroquisCatalogo.estadoColores[e.estado] : null;
    final fillColor = estadoColor?.withValues(alpha: 0.12) ?? info.fill;
    final strokeColor = estadoColor ?? info.stroke;

    canvas.drawRRect(rrect, Paint()..color = fillColor..style = PaintingStyle.fill);

    final strokePaint = Paint()
      ..color = strokeColor
      ..strokeWidth = seleccionado ? 3 : 1.6
      ..style = PaintingStyle.stroke;
    if (info.dashed && !seleccionado) {
      _drawDashedRRect(canvas, rrect, strokePaint);
    } else {
      canvas.drawRRect(rrect, strokePaint);
    }

    if (seleccionado) {
      canvas.drawRRect(rrect.inflate(3), Paint()..color = const Color(0xFF4F46E5)..strokeWidth = 1.5..style = PaintingStyle.stroke);
    }
    if (conectando) {
      canvas.drawRRect(rrect.inflate(5), Paint()..color = const Color(0xFF10B981)..strokeWidth = 2.5..style = PaintingStyle.stroke);
    }

    // Ícono centrado
    final iconPainter = TextPainter(textDirection: TextDirection.ltr)
      ..text = TextSpan(
        text: String.fromCharCode(info.icon.codePoint),
        style: TextStyle(fontSize: (e.w < e.h ? e.w : e.h) * 0.32, fontFamily: info.icon.fontFamily, package: info.icon.fontPackage, color: info.ink),
      )
      ..layout();
    iconPainter.paint(canvas, Offset(-iconPainter.width / 2, -iconPainter.height / 2 - (e.name.isNotEmpty ? 6 : 0)));

    // Nombre/etiqueta
    if (e.name.isNotEmpty) {
      final textPainter = TextPainter(textDirection: TextDirection.ltr, textAlign: TextAlign.center)
        ..text = TextSpan(text: e.name, style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: info.ink))
        ..layout(maxWidth: e.w - 4);
      textPainter.paint(canvas, Offset(-textPainter.width / 2, e.h / 2 - textPainter.height - 4));
    }

    canvas.restore();
  }

  void _drawDashedRRect(Canvas canvas, RRect rrect, Paint paint) {
    const dashWidth = 5.0, dashSpace = 3.0;
    final path = Path()..addRRect(rrect);
    for (final metric in path.computeMetrics()) {
      double distance = 0;
      while (distance < metric.length) {
        canvas.drawPath(metric.extractPath(distance, distance + dashWidth), paint);
        distance += dashWidth + dashSpace;
      }
    }
  }

  @override
  bool shouldRepaint(covariant _CroquisPainter oldDelegate) => true;
}

extension _FirstOrNull<T> on Iterable<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
