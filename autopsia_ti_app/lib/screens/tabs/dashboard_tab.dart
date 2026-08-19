import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart' as fmap;
import 'package:latlong2/latlong.dart' as ll;

/// Pestaña "Dashboard": KPIs de IPRESS y mapa georreferenciado con filtros
/// geográficos y de estado en cascada — espejo de
/// resources/views/usuario/dashboard/mapa_progresion.blade.php
/// (updateRelationalOptions/applyFilters/toggleFoco). El filtro "Año" es el
/// único que recalcula en el servidor (igual que la recarga de página de la
/// web); el resto se filtra en el cliente contra los `markers` que ya bajó
/// /v1/establecimientos/map, cada uno con su 'etapa' real (0/1).
///
/// "Ver solo mapa" (modo foco): en la web, body.modo-foco pone el mapa en
/// position:fixed;inset:0;z-index:800, cubriendo TODO el layout incluyendo
/// el sidebar — no es solo "expandir dentro del contenido". Aquí se replica
/// con un OverlayEntry en el Overlay raíz (por encima del Scaffold completo
/// de MainCampoScreen), y los filtros NO se ocultan: se reposicionan como
/// panel flotante superior, igual que #seccion-filtros en modo-foco.
class DashboardTab extends StatefulWidget {
  final int totalIpress;
  final int sinDiagnostico;
  final int conDiagnostico;
  final List<Map<String, dynamic>> markers;
  final List<String> aniosDisponibles;
  final String anioSeleccionado;
  final ValueChanged<String> onAnioChanged;

  const DashboardTab({
    super.key,
    required this.totalIpress,
    required this.sinDiagnostico,
    required this.conDiagnostico,
    required this.markers,
    required this.aniosDisponibles,
    required this.anioSeleccionado,
    required this.onAnioChanged,
  });

  @override
  State<DashboardTab> createState() => _DashboardTabState();
}

class _DashboardTabState extends State<DashboardTab> {
  bool _filtrosExpandidos = true;
  bool _modoFoco = false;
  OverlayEntry? _focoOverlay;

  String? _etapa; // null=Todas, '0'=Sin diagnóstico, '1'=Con diagnóstico
  String? _departamento;
  String? _red;
  String? _microrred;
  String? _provincia;
  String? _distrito;
  String? _categoria;
  String? _establecimientoId;

  /// Todo cambio de estado pasa por acá para que, si el modo foco está
  /// activo, el overlay (que vive fuera del árbol normal de este widget) se
  /// entere y se redibuje también.
  void _set(VoidCallback fn) {
    setState(fn);
    _focoOverlay?.markNeedsBuild();
  }

  bool _matchEtapa(Map<String, dynamic> m) => _etapa == null || m['etapa'].toString() == _etapa;
  bool _matchDep(Map<String, dynamic> m) => _departamento == null || m['departamento'] == _departamento;
  bool _matchRed(Map<String, dynamic> m) => _red == null || m['red'] == _red;
  bool _matchMRed(Map<String, dynamic> m) => _microrred == null || m['microred'] == _microrred;
  bool _matchProv(Map<String, dynamic> m) => _provincia == null || m['provincia'] == _provincia;
  bool _matchDist(Map<String, dynamic> m) => _distrito == null || m['distrito'] == _distrito;
  bool _matchCat(Map<String, dynamic> m) => _categoria == null || m['categoria'] == _categoria;
  bool _matchEst(Map<String, dynamic> m) => _establecimientoId == null || m['id'].toString() == _establecimientoId;

  bool _matchGeo(Map<String, dynamic> m) =>
      _matchDep(m) && _matchRed(m) && _matchMRed(m) && _matchProv(m) && _matchDist(m) && _matchCat(m) && _matchEst(m);

  List<Map<String, dynamic>> get _markersFiltrados =>
      widget.markers.where((m) => _matchEtapa(m) && _matchGeo(m)).toList();

  /// Igual que applyFilters() en la web: los contadores de las tarjetas KPI
  /// reflejan el subconjunto geográfico visible, no el total global.
  int get _totalGeografico => widget.markers.where(_matchGeo).length;
  int get _sinDiagnosticoFiltrado => widget.markers.where((m) => _matchGeo(m) && m['etapa'] == 0).length;
  int get _conDiagnosticoFiltrado => widget.markers.where((m) => _matchGeo(m) && m['etapa'] == 1).length;

  Set<String> _opciones(String campo, bool Function(Map<String, dynamic>) filtroPrevio) {
    final set = <String>{};
    for (final m in widget.markers) {
      if (!filtroPrevio(m)) continue;
      final valor = m[campo]?.toString();
      if (valor != null && valor.isNotEmpty) set.add(valor);
    }
    return set;
  }

  /// Espejo de buildPopup(e) en mapa_progresion.blade.php: nombre, distrito
  /// — provincia, estado de diagnóstico y si tiene actas registradas.
  void _mostrarInfoEstablecimiento(Map<String, dynamic> m) {
    final conDiagnostico = m['etapa'] == 1;
    final tieneMonitoreo = m['tiene_monitoreo'] == true;
    final totalMonitoreos = m['total_monitoreos'] ?? 0;
    final color = conDiagnostico ? const Color(0xFF4F46E5) : const Color(0xFFEF4444);

    showDialog(
      context: context,
      useRootNavigator: true,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
        contentPadding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
        title: Text(
          m['nombre']?.toString() ?? '',
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${m['distrito'] ?? ''} — ${m['provincia'] ?? ''}',
              style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8), letterSpacing: 0.4),
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
              child: Text(
                conDiagnostico ? 'CON DIAGNÓSTICO SITUACIONAL' : 'SIN DIAGNÓSTICO',
                style: TextStyle(fontSize: 10, fontWeight: FontWeight.w900, color: color, letterSpacing: 0.4),
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Icon(
                  tieneMonitoreo ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
                  size: 16,
                  color: tieneMonitoreo ? const Color(0xFF4F46E5) : const Color(0xFFCBD5E1),
                ),
                const SizedBox(width: 8),
                Text(
                  tieneMonitoreo ? 'Actas de Diagnóstico ($totalMonitoreos)' : 'Sin Actas de Diagnóstico',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: tieneMonitoreo ? const Color(0xFF4338CA) : const Color(0xFF94A3B8),
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cerrar')),
        ],
      ),
    );
  }

  bool get _hayFiltroGeoOEtapa =>
      _etapa != null ||
      _departamento != null ||
      _red != null ||
      _microrred != null ||
      _provincia != null ||
      _distrito != null ||
      _categoria != null ||
      _establecimientoId != null;

  // ── MODO FOCO (pantalla completa real, cubre todo el layout) ──
  void _toggleFoco() {
    if (_modoFoco) {
      _salirFoco();
    } else {
      _entrarFoco();
    }
  }

  void _entrarFoco() {
    final entry = OverlayEntry(builder: (_) => _FocoOverlayContent(state: this));
    Overlay.of(context, rootOverlay: true).insert(entry);
    _focoOverlay = entry;
    setState(() => _modoFoco = true);
  }

  void _salirFoco() {
    _focoOverlay?.remove();
    _focoOverlay = null;
    setState(() => _modoFoco = false);
  }

  @override
  void dispose() {
    _focoOverlay?.remove();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // El mapa en pantalla completa vive en el Overlay raíz (ver _entrarFoco);
    // aquí no se renderiza nada mientras esté activo, para no ocupar espacio
    // detrás de la superposición ni duplicar el mapa.
    if (_modoFoco) return const SizedBox.shrink();

    final filtrados = _markersFiltrados;
    final hayFiltroGeoOEtapa = _hayFiltroGeoOEtapa;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _StatRow(
          label: 'TOTAL IPRESS',
          value: (hayFiltroGeoOEtapa ? _totalGeografico : widget.totalIpress).toString(),
          subtitle: 'ESTABLECIMIENTOS GEORREFERENCIADOS',
          color: const Color(0xFF94A3B8),
          barColor: const Color(0xFFE2E8F0),
          barValue: 1.0,
        ),
        const SizedBox(height: 12),
        _StatRow(
          label: 'SIN DIAGNÓSTICO',
          value: (hayFiltroGeoOEtapa ? _sinDiagnosticoFiltrado : widget.sinDiagnostico).toString(),
          subtitle: 'PENDIENTES DE EVALUACIÓN',
          color: const Color(0xFFEF4444),
          barColor: const Color(0xFFEF4444),
          barValue: widget.totalIpress > 0 ? (widget.sinDiagnostico / widget.totalIpress) : 0.0,
        ),
        const SizedBox(height: 12),
        _StatRow(
          label: 'CON DIAGNÓSTICO SITUACIONAL',
          value: (hayFiltroGeoOEtapa ? _conDiagnosticoFiltrado : widget.conDiagnostico).toString(),
          subtitle: 'EVALUACIONES REGISTRADAS',
          color: const Color(0xFF8B5CF6),
          barColor: const Color(0xFF8B5CF6),
          barValue: widget.totalIpress > 0 ? (widget.conDiagnostico / widget.totalIpress) : 0.0,
        ),
        const SizedBox(height: 16),
        _buildFiltros(),
        const SizedBox(height: 16),

        // === MAPA DE IPRESS (vista normal, incrustado) ===
        Container(
          decoration: BoxDecoration(
            border: Border.all(color: const Color(0xFFE2E8F0)),
            borderRadius: BorderRadius.circular(8),
          ),
          clipBehavior: Clip.antiAlias,
          child: _MapaConMarcadores(
            markers: filtrados,
            height: 340,
            onTapEstablecimiento: _mostrarInfoEstablecimiento,
          ),
        ),
      ],
    );
  }

  Widget _buildFiltros() {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(8),
        color: Colors.white,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          InkWell(
            onTap: () => _set(() => _filtrosExpandidos = !_filtrosExpandidos),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  const Icon(Icons.filter_alt_outlined, size: 16, color: Color(0xFF64748B)),
                  const SizedBox(width: 8),
                  const Text(
                    'FILTROS DE BÚSQUEDA',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const Spacer(),
                  Icon(
                    _filtrosExpandidos ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    size: 18, color: const Color(0xFF94A3B8),
                  ),
                ],
              ),
            ),
          ),
          if (_filtrosExpandidos) ...[
            const Divider(height: 1, color: Color(0xFFE2E8F0)),
            Padding(
              padding: const EdgeInsets.all(16),
              child: _filtrosContenido(),
            ),
          ],
        ],
      ),
    );
  }

  /// Contenido de los filtros — reutilizado tanto en la tarjeta normal como
  /// en el panel flotante del modo foco (compacto: `compacto=true` reduce
  /// anchos para caber en un panel angosto centrado arriba del mapa).
  Widget _filtrosContenido({bool compacto = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Wrap(
          spacing: 12,
          runSpacing: 8,
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            const Text('ESTADO DE DIAGNÓSTICO', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF64748B), letterSpacing: 0.6)),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                _pillBtn('Todas', null, const Color(0xFF1E293B), const Color(0xFF1E293B)),
                _pillBtn('Sin Diagnóstico', '0', const Color(0xFFF1F5F9), const Color(0xFF64748B)),
                _pillBtn('Con Diagnóstico Situacional', '1', const Color(0xFFEEF2FF), const Color(0xFF4F46E5)),
              ],
            ),
          ],
        ),
        const SizedBox(height: 14),
        Wrap(
          spacing: 12,
          runSpacing: 10,
          crossAxisAlignment: WrapCrossAlignment.end,
          children: [
            _filterCol('AÑO', _dropdownAnio()),
            _filterCol('DEPARTAMENTO', _dropdown('Todos', _departamento, _opciones('departamento', (_) => true), (v) => _set(() { _departamento = v; _red = _microrred = _provincia = _distrito = _categoria = _establecimientoId = null; }), width: compacto ? 110 : 130)),
            _filterCol('RED', _dropdown('Todas', _red, _opciones('red', _matchDep), (v) => _set(() { _red = v; _microrred = _distrito = _categoria = _establecimientoId = null; }), width: compacto ? 120 : 140)),
            _filterCol('PROVINCIA', _dropdown('Todas', _provincia, _opciones('provincia', (m) => _matchDep(m) && _matchRed(m)), (v) => _set(() { _provincia = v; _microrred = _distrito = _categoria = _establecimientoId = null; }), width: compacto ? 100 : 120)),
            _filterCol('MICRORRED', _dropdown('Todas', _microrred, _opciones('microred', (m) => _matchDep(m) && _matchRed(m) && _matchProv(m)), (v) => _set(() { _microrred = v; _distrito = _categoria = _establecimientoId = null; }), width: compacto ? 120 : 140)),
          ],
        ),
        const SizedBox(height: 10),
        Wrap(
          spacing: 12,
          runSpacing: 10,
          crossAxisAlignment: WrapCrossAlignment.end,
          children: [
            _filterCol('DISTRITO', _dropdown('Todos', _distrito, _opciones('distrito', (m) => _matchDep(m) && _matchRed(m) && _matchMRed(m) && _matchProv(m)), (v) => _set(() { _distrito = v; _establecimientoId = null; }), width: compacto ? 100 : 120)),
            _filterCol('CATEGORÍA', _dropdown('Todas', _categoria, _opciones('categoria', (m) => _matchDep(m) && _matchRed(m) && _matchMRed(m) && _matchProv(m) && _matchDist(m)), (v) => _set(() { _categoria = v; _establecimientoId = null; }), width: compacto ? 90 : 100)),
            _filterCol('ESTABLECIMIENTO', _dropdownEstablecimiento(width: compacto ? 180 : 220)),
            ElevatedButton.icon(
              onPressed: _toggleFoco,
              style: ElevatedButton.styleFrom(
                backgroundColor: _modoFoco ? const Color(0xFF10B981) : const Color(0xFF1E293B),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                elevation: 0,
              ),
              icon: Icon(_modoFoco ? Icons.close_fullscreen_rounded : Icons.map_outlined, size: 16),
              label: Text(_modoFoco ? 'VISTA NORMAL' : 'VER SOLO MAPA', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ],
    );
  }

  Widget _dropdownAnio() {
    return Container(
      height: 36,
      width: 110,
      padding: const EdgeInsets.symmetric(horizontal: 8),
      decoration: BoxDecoration(border: Border.all(color: const Color(0xFFE2E8F0)), borderRadius: BorderRadius.circular(6), color: Colors.white),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: widget.anioSeleccionado,
          isDense: true,
          isExpanded: true,
          style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B)),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: Color(0xFF94A3B8)),
          items: [
            const DropdownMenuItem(value: 'todos', child: Text('Todos los años', overflow: TextOverflow.ellipsis)),
            ...widget.aniosDisponibles.map((a) => DropdownMenuItem(value: a, child: Text(a))),
          ],
          onChanged: (v) {
            if (v != null) widget.onAnioChanged(v);
          },
        ),
      ),
    );
  }

  Widget _dropdownEstablecimiento({double width = 220}) {
    final opciones = widget.markers.where((m) =>
        _matchDep(m) && _matchRed(m) && _matchMRed(m) && _matchProv(m) && _matchDist(m) && _matchCat(m));

    return Container(
      height: 36,
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 8),
      decoration: BoxDecoration(border: Border.all(color: const Color(0xFFE2E8F0)), borderRadius: BorderRadius.circular(6), color: Colors.white),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String?>(
          value: _establecimientoId,
          isDense: true,
          isExpanded: true,
          hint: const Text('Todos', style: TextStyle(fontSize: 13)),
          style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B)),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: Color(0xFF94A3B8)),
          items: [
            const DropdownMenuItem(value: null, child: Text('Todos', overflow: TextOverflow.ellipsis)),
            ...opciones.map((m) => DropdownMenuItem(value: m['id'].toString(), child: Text(m['nombre']?.toString() ?? '', overflow: TextOverflow.ellipsis))),
          ],
          onChanged: (v) => _set(() => _establecimientoId = v),
        ),
      ),
    );
  }

  Widget _dropdown(String etiquetaTodos, String? valor, Set<String> opciones, ValueChanged<String?> onChanged, {double width = 130}) {
    final lista = opciones.toList()..sort();
    return Container(
      height: 36,
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 8),
      decoration: BoxDecoration(border: Border.all(color: const Color(0xFFE2E8F0)), borderRadius: BorderRadius.circular(6), color: Colors.white),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String?>(
          value: valor,
          isDense: true,
          isExpanded: true,
          hint: Text(etiquetaTodos, style: const TextStyle(fontSize: 13)),
          style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B)),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: Color(0xFF94A3B8)),
          items: [
            DropdownMenuItem(value: null, child: Text(etiquetaTodos, overflow: TextOverflow.ellipsis)),
            ...lista.map((e) => DropdownMenuItem(value: e, child: Text(e, style: const TextStyle(fontSize: 13), overflow: TextOverflow.ellipsis, maxLines: 1))),
          ],
          onChanged: onChanged,
        ),
      ),
    );
  }

  Widget _filterCol(String label, Widget field) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF94A3B8), letterSpacing: 0.6)),
        const SizedBox(height: 4),
        field,
      ],
    );
  }

  Widget _pillBtn(String label, String? valorEtapa, Color bg, Color fg) {
    final isActive = _etapa == valorEtapa;
    return GestureDetector(
      onTap: () => _set(() => _etapa = valorEtapa),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? bg : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: isActive && valorEtapa == '1' ? const Color(0xFF4F46E5) : Colors.transparent),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: isActive ? (bg == const Color(0xFF1E293B) ? Colors.white : fg) : const Color(0xFF64748B),
          ),
        ),
      ),
    );
  }
}

/// Contenido del Overlay de pantalla completa — cubre TODO (incluido el
/// sidebar de MainCampoScreen), igual que body.modo-foco en la web. Escucha
/// directamente el estado de `_DashboardTabState` (misma instancia, mismos
/// filtros en vivo) para no duplicar la lógica de filtrado.
class _FocoOverlayContent extends StatelessWidget {
  final _DashboardTabState state;
  const _FocoOverlayContent({required this.state});

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: Material(
        color: Colors.white,
        child: CallbackShortcuts(
          bindings: {const SingleActivator(LogicalKeyboardKey.escape): state._salirFoco},
          child: Focus(
            autofocus: true,
            child: Stack(
              children: [
                Positioned.fill(
                  child: _MapaConMarcadores(
                    markers: state._markersFiltrados,
                    height: double.infinity,
                    onTapEstablecimiento: state._mostrarInfoEstablecimiento,
                  ),
                ),
                // Panel de filtros flotante (igual que #seccion-filtros en modo-foco: fixed, top:16px, centrado)
                Positioned(
                  top: 16,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: ConstrainedBox(
                      constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.94, maxHeight: MediaQuery.of(context).size.height - 90),
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.97),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: const [BoxShadow(color: Color(0x30000000), blurRadius: 40, offset: Offset(0, 8))],
                          border: Border.all(color: Colors.white),
                        ),
                        padding: const EdgeInsets.all(16),
                        child: SingleChildScrollView(child: state._filtrosContenido(compacto: true)),
                      ),
                    ),
                  ),
                ),
                // Botón salir (igual que #btn-salir-foco)
                Positioned(
                  top: 28,
                  right: 24,
                  child: ElevatedButton.icon(
                    onPressed: state._salirFoco,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E293B),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(50)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                    icon: const Icon(Icons.close_rounded, size: 16),
                    label: const Text('Salir del modo mapa', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.4)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Mapa + badge de conteo + leyenda flotante — compartido entre la vista
/// normal (incrustada, altura fija) y el modo foco (pantalla completa).
///
/// Espejo del auto-encuadre de applyFilters()/fitBounds() en
/// mapa_progresion.blade.php: al cambiar el conjunto de marcadores visibles
/// (por un filtro) la cámara se reencuadra sola (fitBounds con margen,
/// maxZoom 13), y si queda un único establecimiento visible (filtro por
/// Establecimiento) hace zoom directo a nivel 15 sobre ese punto.
class _MapaConMarcadores extends StatefulWidget {
  final List<Map<String, dynamic>> markers;
  final double height;
  final void Function(Map<String, dynamic>) onTapEstablecimiento;

  const _MapaConMarcadores({required this.markers, required this.height, required this.onTapEstablecimiento});

  @override
  State<_MapaConMarcadores> createState() => _MapaConMarcadoresState();
}

class _MapaConMarcadoresState extends State<_MapaConMarcadores> {
  final _mapController = fmap.MapController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _encuadrar(widget.markers));
  }

  @override
  void didUpdateWidget(covariant _MapaConMarcadores oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (!identical(widget.markers, oldWidget.markers)) {
      WidgetsBinding.instance.addPostFrameCallback((_) => _encuadrar(widget.markers));
    }
  }

  List<ll.LatLng> _puntosValidos(List<Map<String, dynamic>> markers) {
    return markers
        .map((m) => ll.LatLng(double.tryParse(m['latitud']?.toString() ?? '') ?? 0.0, double.tryParse(m['longitud']?.toString() ?? '') ?? 0.0))
        .where((p) => p.latitude != 0.0 || p.longitude != 0.0)
        .toList();
  }

  void _encuadrar(List<Map<String, dynamic>> markers) {
    final puntos = _puntosValidos(markers);
    if (puntos.isEmpty) return;
    if (puntos.length == 1) {
      _mapController.move(puntos.first, 15);
      return;
    }
    try {
      _mapController.fitCamera(fmap.CameraFit.bounds(
        bounds: fmap.LatLngBounds.fromPoints(puntos),
        padding: const EdgeInsets.all(40),
        maxZoom: 13,
      ));
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        SizedBox(
          height: widget.height,
          width: double.infinity,
          child: fmap.FlutterMap(
            mapController: _mapController,
            options: const fmap.MapOptions(
              initialCenter: ll.LatLng(-14.07, -75.73),
              initialZoom: 9,
            ),
            children: [
              fmap.TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.autopsiati.app',
              ),
              fmap.MarkerLayer(
                markers: widget.markers.map((m) {
                  final lat = double.tryParse(m['latitud']?.toString() ?? '') ?? 0.0;
                  final lng = double.tryParse(m['longitud']?.toString() ?? '') ?? 0.0;
                  final conDiagnostico = m['etapa'] == 1;
                  final tamanoPunto = conDiagnostico ? 22.0 : 12.0;
                  const tamanoToque = 34.0;
                  return fmap.Marker(
                    point: ll.LatLng(lat, lng),
                    width: tamanoToque,
                    height: tamanoToque,
                    child: GestureDetector(
                      behavior: HitTestBehavior.opaque,
                      onTap: () => widget.onTapEstablecimiento(m),
                      child: Center(
                        child: Container(
                          width: tamanoPunto,
                          height: tamanoPunto,
                          decoration: BoxDecoration(
                            color: conDiagnostico ? const Color(0xFF4F46E5) : const Color(0xFFEF4444),
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: conDiagnostico ? 3 : 2),
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ),
        ),
        Positioned(
          top: 10,
          left: 10,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(6),
              boxShadow: const [BoxShadow(color: Color(0x20000000), blurRadius: 6)],
            ),
            child: Text(
              'MOSTRANDO  ${widget.markers.length}  EESS',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
          ),
        ),
        Positioned(
          bottom: 10,
          right: 10,
          child: Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.95),
              borderRadius: BorderRadius.circular(12),
              boxShadow: const [BoxShadow(color: Color(0x20000000), blurRadius: 8)],
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                _LeyendaItem(color: Color(0xFFEF4444), label: 'Sin Diagnóstico'),
                SizedBox(height: 4),
                _LeyendaItem(color: Color(0xFF4F46E5), label: 'Con Diagnóstico Situacional'),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _LeyendaItem extends StatelessWidget {
  final Color color;
  final String label;
  const _LeyendaItem({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle, border: Border.all(color: Colors.white, width: 1.5))),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
      ],
    );
  }
}

class _StatRow extends StatelessWidget {
  final String label;
  final String value;
  final String subtitle;
  final Color color;
  final Color barColor;
  final double barValue;

  const _StatRow({
    required this.label,
    required this.value,
    required this.subtitle,
    required this.color,
    required this.barColor,
    required this.barValue,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: color == const Color(0xFF94A3B8) ? const Color(0xFF94A3B8) : color,
                    letterSpacing: 0.8,
                  ),
                ),
              ),
              Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 36,
              fontWeight: FontWeight.w900,
              color: color == const Color(0xFF94A3B8) ? const Color(0xFF1E293B) : color,
              height: 1.1,
            ),
          ),
          const SizedBox(height: 6),
          LinearProgressIndicator(
            value: barValue,
            minHeight: 3,
            backgroundColor: const Color(0xFFE2E8F0),
            valueColor: AlwaysStoppedAnimation<Color>(barColor),
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF94A3B8), letterSpacing: 0.6),
          ),
        ],
      ),
    );
  }
}
