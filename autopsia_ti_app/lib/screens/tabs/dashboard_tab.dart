import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart' as fmap;
import 'package:latlong2/latlong.dart' as ll;

/// Pestaña "Dashboard": KPIs de IPRESS y mapa georreferenciado.
class DashboardTab extends StatelessWidget {
  final int totalIpress;
  final int sinDiagnostico;
  final int conDiagnostico;
  final List<Map<String, dynamic>> markers;

  const DashboardTab({
    super.key,
    required this.totalIpress,
    required this.sinDiagnostico,
    required this.conDiagnostico,
    required this.markers,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // === TARJETA 1: TOTAL IPRESS ===
        _StatRow(
          label: 'TOTAL IPRESS',
          value: totalIpress.toString(),
          subtitle: 'ESTABLECIMIENTOS GEORREFERENCIADOS',
          color: const Color(0xFF94A3B8),
          barColor: const Color(0xFFE2E8F0),
          barValue: 1.0,
        ),
        const SizedBox(height: 12),

        // === TARJETA 2: SIN DIAGNÓSTICO ===
        _StatRow(
          label: 'SIN DIAGNÓSTICO',
          value: sinDiagnostico.toString(),
          subtitle: 'PENDIENTES DE EVALUACIÓN',
          color: const Color(0xFFEF4444),
          barColor: const Color(0xFFEF4444),
          barValue: totalIpress > 0 ? (sinDiagnostico / totalIpress) : 0.0,
        ),
        const SizedBox(height: 12),

        // === TARJETA 3: CON DIAGNÓSTICO SITUACIONAL ===
        _StatRow(
          label: 'CON DIAGNÓSTICO SITUACIONAL',
          value: conDiagnostico.toString(),
          subtitle: 'EVALUACIONES REGISTRADAS',
          color: const Color(0xFF8B5CF6),
          barColor: const Color(0xFF8B5CF6),
          barValue: totalIpress > 0 ? (conDiagnostico / totalIpress) : 0.0,
        ),
        const SizedBox(height: 16),

        // === FILTROS DE BÚSQUEDA (PLEGABLE) ===
        const _DashboardFiltros(),
        const SizedBox(height: 16),

        // === MAPA DE IPRESS ===
        Container(
          decoration: BoxDecoration(
            border: Border.all(color: const Color(0xFFE2E8F0)),
            borderRadius: BorderRadius.circular(8),
          ),
          clipBehavior: Clip.antiAlias,
          child: Stack(
            children: [
              SizedBox(
                height: 340,
                child: fmap.FlutterMap(
                  options: fmap.MapOptions(
                    initialCenter: ll.LatLng(-9.19, -75.015),
                    initialZoom: 5.5,
                  ),
                  children: [
                    fmap.TileLayer(
                      urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                      userAgentPackageName: 'com.autopsiati.app',
                    ),
                    fmap.MarkerLayer(
                      markers: markers.map((m) {
                        final lat = double.tryParse(m['latitud']?.toString() ?? '') ?? 0.0;
                        final lng = double.tryParse(m['longitud']?.toString() ?? '') ?? 0.0;
                        return fmap.Marker(
                          point: ll.LatLng(lat, lng),
                          width: 12,
                          height: 12,
                          child: Container(
                            decoration: const BoxDecoration(color: Color(0xFFEF4444), shape: BoxShape.circle),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                ),
              ),
              // Badge "MOSTRANDO XXX IPRESS"
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
                    'MOSTRANDO  $totalIpress  IPRESS',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                ),
              ),
            ],
          ),
        ),
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

// ===========================================================================
// WIDGET DE FILTROS DEL DASHBOARD (PLEGABLE - IDÉNTICO A LARAVEL)
// ===========================================================================
class _DashboardFiltros extends StatefulWidget {
  const _DashboardFiltros();

  @override
  State<_DashboardFiltros> createState() => _DashboardFiltrosState();
}

class _DashboardFiltrosState extends State<_DashboardFiltros> {
  bool _expanded = true;
  String _estadoDx = 'TODAS';
  String _anio = 'Todos los años';
  String _departamento = 'Todos';
  String _red = 'Todas';
  String _microrred = 'Todas';
  String _provincia = 'Todas';
  String _distrito = 'Todos';
  String _categoria = 'Todas';
  String _establecimiento = 'Todos';

  Widget _dropdown(String value, List<String> items, ValueChanged<String?> onChanged, {double width = 130}) {
    return Container(
      height: 36,
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 8),
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(6),
        color: Colors.white,
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isDense: true,
          isExpanded: true,
          style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B)),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: Color(0xFF94A3B8)),
          items: items.map((e) => DropdownMenuItem(
            value: e,
            child: Text(
              e,
              style: const TextStyle(fontSize: 13),
              overflow: TextOverflow.ellipsis,
              maxLines: 1,
            ),
          )).toList(),
          onChanged: onChanged,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(8),
        color: Colors.white,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // HEADER DEL PANEL (PLEGABLE)
          InkWell(
            onTap: () => setState(() => _expanded = !_expanded),
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
                    _expanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    size: 18, color: const Color(0xFF94A3B8),
                  ),
                ],
              ),
            ),
          ),

          if (_expanded) ...[
            const Divider(height: 1, color: Color(0xFFE2E8F0)),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ESTADO DE DIAGNÓSTICO: Botones tipo pill
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
                          _pillBtn('TODAS', const Color(0xFF1E293B), const Color(0xFF1E293B)),
                          _pillBtn('SIN DIAGNÓSTICO', const Color(0xFFF1F5F9), const Color(0xFF64748B)),
                          _pillBtn('CON DIAGNÓSTICO SITUACIONAL', const Color(0xFFEEF2FF), const Color(0xFF4F46E5)),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // FILA 1: AÑO / DEPARTAMENTO / RED / MICRORRED / PROVINCIA
                  Wrap(
                    spacing: 12,
                    runSpacing: 10,
                    crossAxisAlignment: WrapCrossAlignment.end,
                    children: [
                      _filterCol('AÑO', _dropdown(_anio, ['Todos los años', '2026', '2025', '2024'], (v) => setState(() => _anio = v!), width: 110)),
                      _filterCol('DEPARTAMENTO', _dropdown(_departamento, ['Todos', 'Lima', 'Arequipa', 'Cusco', 'Piura', 'La Libertad'], (v) => setState(() => _departamento = v!), width: 110)),
                      _filterCol('RED', _dropdown(_red, ['Todas', 'Red Lima Norte', 'Red Lima Sur', 'Red Lima Este'], (v) => setState(() => _red = v!), width: 130)),
                      _filterCol('MICRORRED', _dropdown(_microrred, ['Todas', 'Microrred Rímac', 'Microrred Miraflores'], (v) => setState(() => _microrred = v!), width: 130)),
                      _filterCol('PROVINCIA', _dropdown(_provincia, ['Todas', 'Lima', 'Callao', 'Arequipa', 'Trujillo'], (v) => setState(() => _provincia = v!), width: 110)),
                    ],
                  ),
                  const SizedBox(height: 10),

                  // FILA 2: DISTRITO / CATEGORÍA / ESTABLECIMIENTO / BOTÓN
                  Wrap(
                    spacing: 12,
                    runSpacing: 10,
                    crossAxisAlignment: WrapCrossAlignment.end,
                    children: [
                      _filterCol('DISTRITO', _dropdown(_distrito, ['Todos', 'Rímac', 'San Juan de Lurigancho', 'Villa El Salvador'], (v) => setState(() => _distrito = v!), width: 110)),
                      _filterCol('CATEGORÍA', _dropdown(_categoria, ['Todas', 'I-1', 'I-2', 'I-3', 'I-4', 'II-1', 'II-2', 'III-1', 'III-2'], (v) => setState(() => _categoria = v!), width: 90)),
                      _filterCol('ESTABLECIMIENTO', _dropdown(_establecimiento, ['Todos', 'Hospital Nacional Dos de Mayo', 'Clínica Good Hope'], (v) => setState(() => _establecimiento = v!), width: 200)),
                      ElevatedButton.icon(
                        onPressed: () {},
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1E293B),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                          elevation: 0,
                        ),
                        icon: const Icon(Icons.map_outlined, size: 16),
                        label: const Text('VER SOLO MAPA', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ],
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

  Widget _pillBtn(String label, Color bg, Color fg) {
    final isActive = _estadoDx == label;
    return GestureDetector(
      onTap: () => setState(() => _estadoDx = label),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? bg : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: isActive && label == 'CON DIAGNÓSTICO SITUACIONAL' ? const Color(0xFF4F46E5) : Colors.transparent),
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
