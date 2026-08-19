import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../repositories/acta_repository.dart';
import '../acta_detalle_screen.dart';

/// Pestaña "Actas de Diagnóstico Situacional" — espejo real de
/// MonitoreoController::index / resources/views/usuario/monitoreo/index.blade.php.
/// Antes esta pestaña mostraba el buscador de establecimientos para CREAR
/// una acta (esa es en realidad una pantalla aparte, `/crear-acta`); esta es
/// la pantalla real: el LISTADO de actas ya existentes, con las mismas 9
/// columnas, filtros, contadores y acción de anular/reactivar.
///
/// Deliberadamente NO incluye (requieren sesión web o son workflows de
/// oficina, no de campo): "Subir acta consolidada firmada", "Enviar por
/// correo", "Ver PDF" (la ruta real exige sesión de navegador autenticada,
/// no el token de la app), "Cambiar autor" (solo 5 DNIs hardcodeados en el
/// propio Laravel). "Gestionar módulos"/"Editar" solo abren el detalle si el
/// acta fue capturada en ESTE dispositivo — de lo contrario se muestra un
/// resumen de solo lectura.
class ActasMonitoreoListadoTab extends StatefulWidget {
  final VoidCallback onNuevaActa;
  final VoidCallback onSincronizar;
  final bool isSyncing;

  const ActasMonitoreoListadoTab({
    super.key,
    required this.onNuevaActa,
    required this.onSincronizar,
    required this.isSyncing,
  });

  @override
  State<ActasMonitoreoListadoTab> createState() => _ActasMonitoreoListadoTabState();
}

class _ActasMonitoreoListadoTabState extends State<ActasMonitoreoListadoTab> {
  final _repo = ActaRepository();

  bool _cargando = true;
  bool _mostrarFiltros = false;
  List<Map<String, dynamic>> _actas = [];
  List<Map<String, dynamic>> _pendientesLocal = [];
  Map<String, dynamic> _counts = {'completados': 0, 'pendientes': 0, 'anuladas': 0};
  Map<String, dynamic> _filtrosDisponibles = {};
  int _currentPage = 1;
  int _lastPage = 1;
  bool _puedeVerFiltros = true;

  String? _implementador;
  String? _provincia;
  String? _distrito;
  String? _establecimientoId;
  String? _estado;
  String _estadoAnulado = 'todos';
  DateTime _fechaInicio = DateTime(DateTime.now().year, 1, 1);
  DateTime _fechaFin = DateTime.now();

  @override
  void initState() {
    super.initState();
    _cargarRol();
    _cargar();
    _cargarPendientesLocal();
  }

  Future<void> _cargarRol() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() => _puedeVerFiltros = prefs.getString('user_role') != 'operador');
  }

  Future<void> _cargarPendientesLocal() async {
    final pendientes = await _repo.obtenerPendientes();
    if (!mounted) return;
    setState(() => _pendientesLocal = pendientes);
  }

  String _fmtFecha(DateTime d) => '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _cargar({int page = 1}) async {
    setState(() => _cargando = true);
    final filtros = <String, String>{
      'page': '$page',
      'fecha_inicio': _fmtFecha(_fechaInicio),
      'fecha_fin': _fmtFecha(_fechaFin),
      'estado_anulado': _estadoAnulado,
      if (_implementador != null) 'implementador': _implementador!,
      if (_provincia != null) 'provincia': _provincia!,
      if (_distrito != null) 'distrito': _distrito!,
      if (_establecimientoId != null) 'establecimiento_id': _establecimientoId!,
      if (_estado != null) 'estado': _estado!,
    };
    final res = await _repo.obtenerActasMonitoreo(filtros);
    if (!mounted) return;
    setState(() {
      _actas = List<Map<String, dynamic>>.from(res['actas'] ?? []);
      _counts = Map<String, dynamic>.from(res['counts'] ?? {'completados': 0, 'pendientes': 0, 'anuladas': 0});
      _filtrosDisponibles = Map<String, dynamic>.from(res['filtros'] ?? {});
      _currentPage = res['current_page'] ?? 1;
      _lastPage = res['last_page'] ?? 1;
      _cargando = false;
    });
  }

  void _limpiarFiltros() {
    setState(() {
      _implementador = null;
      _provincia = null;
      _distrito = null;
      _establecimientoId = null;
      _estado = null;
      _estadoAnulado = 'todos';
      _fechaInicio = DateTime(DateTime.now().year, 1, 1);
      _fechaFin = DateTime.now();
    });
    _cargar();
  }

  Future<void> _confirmarAnular(Map<String, dynamic> acta) async {
    final anulado = acta['anulado'] == true;
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(anulado ? '¿Reactivar Acta?' : '¿Anular Acta?'),
        content: Text(anulado
            ? 'El acta N° ${acta['numero_acta']} volverá a estar activa.'
            : 'El acta N° ${acta['numero_acta']} se marcará como anulada.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: anulado ? const Color(0xFF10B981) : const Color(0xFFEF4444), foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(anulado ? 'Reactivar' : 'Anular'),
          ),
        ],
      ),
    );
    if (confirmar != true) return;

    final res = await _repo.anularActaMonitoreo(acta['id'] as int);
    if (!mounted) return;
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(backgroundColor: const Color(0xFF15803D), content: Text(res['message'] ?? 'Listo.')));
      _cargar(page: _currentPage);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(backgroundColor: const Color(0xFFB91C1C), content: Text(res['message'] ?? 'No se pudo actualizar el acta.')));
    }
  }

  /// Abre cualquier acta que el usuario pueda ver en este listado, se haya
  /// creado en Laravel, en este dispositivo, o en otro — la única condición
  /// real es el acceso (ya filtrado por el propio listado según rol). Si no
  /// hay copia local todavía, la descarga completa (módulos/equipos) antes
  /// de abrir el detalle.
  Future<void> _abrirActa(Map<String, dynamic> acta) async {
    final id = acta['id'] as int;
    String? offlineId = (await _repo.obtenerPorIdServidor(id))?.offlineId;
    if (!mounted) return;

    if (offlineId == null) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const AlertDialog(
          content: Row(children: [
            SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)),
            SizedBox(width: 14),
            Expanded(child: Text('Descargando acta…')),
          ]),
        ),
      );
      offlineId = await _repo.descargarActaServidor(id);
      if (!mounted) return;
      Navigator.pop(context); // cierra el diálogo de "Descargando…"

      if (offlineId == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          backgroundColor: Color(0xFFB91C1C),
          content: Text('No se pudo descargar el acta. Verifique su conexión e intente de nuevo.'),
        ));
        return;
      }
    }

    if (!mounted) return;
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ActaDetalleScreen(offlineId: offlineId!, establecimientoNombre: acta['establecimiento_nombre'] ?? '')),
    );
    _cargar(page: _currentPage);
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _tarjetaKpis(),
            if (_pendientesLocal.isNotEmpty) ...[
              const SizedBox(height: 16),
              _bannerPendientesLocal(),
            ],
            const SizedBox(height: 20),
            if (_puedeVerFiltros) ...[
              _filtrosCard(),
              const SizedBox(height: 20),
            ],
            if (_cargando)
              const Padding(padding: EdgeInsets.symmetric(vertical: 60), child: Center(child: CircularProgressIndicator()))
            else if (_actas.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(32),
                decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE2E8F0))),
                child: const Column(
                  children: [
                    Icon(Icons.assignment_outlined, size: 40, color: Color(0xFFCBD5E1)),
                    SizedBox(height: 10),
                    Text('No se encontraron actas con estos filtros.', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                  ],
                ),
              )
            else ...[
              _tabla(),
              if (_lastPage > 1) _paginacion(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _tarjetaKpis() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFF1D4ED8), Color(0xFF4F46E5)], begin: Alignment.centerLeft, end: Alignment.centerRight),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        runSpacing: 14,
        spacing: 14,
        children: [
          Wrap(
            spacing: 10,
            runSpacing: 8,
            children: [
              _kpiChip('TOTAL', '${_currentPage == 1 && _lastPage == 1 ? _actas.length : ((_counts['completados'] ?? 0) + (_counts['pendientes'] ?? 0) + (_counts['anuladas'] ?? 0))}', const Color(0xFF0F172A), Colors.white),
              _kpiChip('FIRMADAS', '${_counts['completados'] ?? 0}', const Color(0x3310B981), const Color(0xFF34D399)),
              _kpiChip('PENDIENTES', '${_counts['pendientes'] ?? 0}', const Color(0x33F59E0B), const Color(0xFFFBBF24)),
              _kpiChip('ANULADAS', '${_counts['anuladas'] ?? 0}', const Color(0xFF0F172A), const Color(0xFF94A3B8)),
            ],
          ),
          Wrap(
            spacing: 10,
            children: [
              if (_puedeVerFiltros)
                OutlinedButton.icon(
                  onPressed: () => setState(() => _mostrarFiltros = !_mostrarFiltros),
                  style: OutlinedButton.styleFrom(foregroundColor: Colors.white, side: const BorderSide(color: Colors.white54)),
                  icon: const Icon(Icons.filter_alt_outlined, size: 16),
                  label: const Text('Filtros'),
                ),
              ElevatedButton.icon(
                onPressed: widget.isSyncing ? null : widget.onSincronizar,
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF10B981), foregroundColor: Colors.white),
                icon: const Icon(Icons.cloud_download_outlined, size: 16),
                label: Text(widget.isSyncing ? 'Sincronizando…' : 'Offline'),
              ),
              ElevatedButton.icon(
                onPressed: widget.onNuevaActa,
                style: ElevatedButton.styleFrom(backgroundColor: Colors.white, foregroundColor: const Color(0xFF1D4ED8)),
                icon: const Icon(Icons.bolt_rounded, size: 16),
                label: const Text('Nueva Acta', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _kpiChip(String label, String value, Color bg, Color fg) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(10)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(value, style: TextStyle(color: fg, fontSize: 18, fontWeight: FontWeight.w900)),
          Text(label, style: TextStyle(color: fg.withValues(alpha: 0.85), fontSize: 9, fontWeight: FontWeight.w800, letterSpacing: 0.5)),
        ],
      ),
    );
  }

  Widget _bannerPendientesLocal() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(color: const Color(0xFFFFFBEB), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFFDE68A))),
      child: Row(
        children: [
          const Icon(Icons.cloud_off_rounded, color: Color(0xFFB45309), size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              '${_pendientesLocal.length} acta(s) capturada(s) en este dispositivo aún no sincronizada(s) — no aparecen en este listado hasta subirse al servidor.',
              style: const TextStyle(color: Color(0xFF92400E), fontSize: 12, fontWeight: FontWeight.w600),
            ),
          ),
          TextButton(onPressed: widget.isSyncing ? null : widget.onSincronizar, child: const Text('Sincronizar ahora')),
        ],
      ),
    );
  }

  Widget _filtrosCard() {
    if (!_mostrarFiltros) return const SizedBox.shrink();
    final implementadores = List<String>.from(_filtrosDisponibles['implementadores'] ?? []);
    final provincias = List<String>.from(_filtrosDisponibles['provincias'] ?? []);
    final distritos = List<String>.from(_filtrosDisponibles['distritos'] ?? []);
    final establecimientos = List<Map<String, dynamic>>.from(_filtrosDisponibles['establecimientos'] ?? []);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE2E8F0))),
      child: Wrap(
        spacing: 12,
        runSpacing: 12,
        crossAxisAlignment: WrapCrossAlignment.end,
        children: [
          _dropdownFiltro('Implementador', _implementador, implementadores, (v) => setState(() => _implementador = v)),
          _dropdownFiltro('Provincia', _provincia, provincias, (v) => setState(() {
                _provincia = v;
                _distrito = null;
              })),
          _dropdownFiltro('Distrito', _distrito, distritos, (v) => setState(() => _distrito = v)),
          _dropdownFiltro(
            'Establecimiento',
            _establecimientoId,
            establecimientos.map((e) => e['nombre'].toString()).toList(),
            (v) => setState(() => _establecimientoId = v == null ? null : establecimientos.firstWhere((e) => e['nombre'] == v)['id'].toString()),
          ),
          _dropdownFiltro('Estado', _estado, const ['firmada', 'pendiente'], (v) => setState(() => _estado = v), etiquetas: const {'firmada': 'FIRMADO', 'pendiente': 'PENDIENTE'}),
          SizedBox(
            width: 130,
            child: DropdownButtonFormField<String>(
              initialValue: _estadoAnulado,
              decoration: const InputDecoration(labelText: 'Visibilidad', isDense: true, filled: true, fillColor: Colors.white),
              items: const [
                DropdownMenuItem(value: 'todos', child: Text('Todas')),
                DropdownMenuItem(value: 'activo', child: Text('Activas')),
                DropdownMenuItem(value: 'anulado', child: Text('Anuladas')),
              ],
              onChanged: (v) => setState(() => _estadoAnulado = v ?? 'todos'),
            ),
          ),
          _fechaPicker('Desde', _fechaInicio, (d) => setState(() => _fechaInicio = d)),
          _fechaPicker('Hasta', _fechaFin, (d) => setState(() => _fechaFin = d)),
          ElevatedButton.icon(
            onPressed: () => _cargar(),
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
            icon: const Icon(Icons.search_rounded, size: 16),
            label: const Text('Buscar'),
          ),
          TextButton.icon(onPressed: _limpiarFiltros, icon: const Icon(Icons.refresh_rounded, size: 16), label: const Text('Limpiar')),
        ],
      ),
    );
  }

  Widget _dropdownFiltro(String label, String? valor, List<String> opciones, ValueChanged<String?> onChanged, {Map<String, String>? etiquetas}) {
    return SizedBox(
      width: 160,
      child: DropdownButtonFormField<String>(
        initialValue: valor,
        isExpanded: true,
        decoration: InputDecoration(labelText: label, isDense: true, filled: true, fillColor: Colors.white),
        hint: const Text('Todos'),
        items: [
          const DropdownMenuItem(value: null, child: Text('Todos')),
          ...opciones.map((o) => DropdownMenuItem(value: o, child: Text(etiquetas?[o] ?? o, overflow: TextOverflow.ellipsis))),
        ],
        onChanged: onChanged,
      ),
    );
  }

  Widget _fechaPicker(String label, DateTime valor, ValueChanged<DateTime> onChanged) {
    return SizedBox(
      width: 140,
      child: InkWell(
        onTap: () async {
          final res = await showDatePicker(context: context, initialDate: valor, firstDate: DateTime(2020), lastDate: DateTime(2100));
          if (res != null) onChanged(res);
        },
        child: InputDecorator(
          decoration: InputDecoration(labelText: label, isDense: true, filled: true, fillColor: Colors.white),
          child: Text('${valor.day.toString().padLeft(2, '0')}/${valor.month.toString().padLeft(2, '0')}/${valor.year}'),
        ),
      ),
    );
  }

  Widget _tabla() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 40,
        dataRowMinHeight: 60,
        dataRowMaxHeight: 66,
        horizontalMargin: 12,
        columnSpacing: 22,
        headingTextStyle: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.5),
        columns: const [
          DataColumn(label: Text('N° ACTA')),
          DataColumn(label: Text('FECHA')),
          DataColumn(label: Text('ESTABLECIMIENTO')),
          DataColumn(label: Text('PROV./DIST.')),
          DataColumn(label: Text('IMPLEMENTADOR')),
          DataColumn(label: Text('MÓDULOS FIRMADOS')),
          DataColumn(label: Text('ACTA CONSOLIDADA')),
          DataColumn(label: Text('ACCIONES')),
        ],
        rows: _actas.map((a) {
          final anulado = a['anulado'] == true;
          final firmado = a['firmado'] == true;
          final mod = Map<String, dynamic>.from(a['modulos_firmados'] ?? {'firmados': 0, 'total': 0, 'porcentaje': 0});
          return DataRow(
            color: anulado ? WidgetStateProperty.all(const Color(0xFFF8FAFC)) : null,
            cells: [
              DataCell(Text('${a['numero_acta']}', style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold))),
              DataCell(Text('${a['fecha']}')),
              DataCell(SizedBox(
                width: 170,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('${a['establecimiento_nombre']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12), overflow: TextOverflow.ellipsis),
                    Row(children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                        decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(4)),
                        child: Text('${a['categoria_congelada'] ?? '—'}', style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold)),
                      ),
                      if (anulado) ...[
                        const SizedBox(width: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                          decoration: BoxDecoration(color: const Color(0xFFFEE2E2), borderRadius: BorderRadius.circular(4)),
                          child: const Text('ANULADA', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w900, color: Color(0xFFB91C1C))),
                        ),
                      ],
                    ]),
                  ],
                ),
              )),
              DataCell(Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text('${a['provincia']}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  Text('${a['distrito']}', style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
                ],
              )),
              DataCell(SizedBox(width: 140, child: Text('${a['implementador'] ?? 'NO ASIGNADO'}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis))),
              DataCell(SizedBox(
                width: 110,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('${mod['firmados']}/${mod['total']}  ${mod['porcentaje']}%', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 3),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: (mod['total'] ?? 0) > 0 ? (mod['firmados'] / mod['total']) : 0,
                        minHeight: 5,
                        backgroundColor: const Color(0xFFE2E8F0),
                        valueColor: AlwaysStoppedAnimation(mod['porcentaje'] == 100 ? const Color(0xFF10B981) : const Color(0xFFFBBF24)),
                      ),
                    ),
                  ],
                ),
              )),
              DataCell(Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(color: firmado ? const Color(0xFFD1FAE5) : const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(20)),
                child: Text(firmado ? 'FIRMADA' : 'PENDIENTE', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w900, color: firmado ? const Color(0xFF047857) : const Color(0xFF64748B))),
              )),
              DataCell(Row(mainAxisSize: MainAxisSize.min, children: [
                IconButton(
                  tooltip: 'Gestionar módulos',
                  icon: const Icon(Icons.layers_outlined, size: 18, color: Color(0xFF4F46E5)),
                  onPressed: () => _abrirActa(a),
                ),
                IconButton(
                  tooltip: anulado ? 'Reactivar acta' : 'Anular acta',
                  icon: Icon(anulado ? Icons.settings_backup_restore_rounded : Icons.block_rounded, size: 18, color: anulado ? const Color(0xFF10B981) : const Color(0xFFEF4444)),
                  onPressed: () => _confirmarAnular(a),
                ),
              ])),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _paginacion() {
    return Padding(
      padding: const EdgeInsets.only(top: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          IconButton(onPressed: _currentPage > 1 ? () => _cargar(page: _currentPage - 1) : null, icon: const Icon(Icons.chevron_left_rounded)),
          Text('Página $_currentPage de $_lastPage', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
          IconButton(onPressed: _currentPage < _lastPage ? () => _cargar(page: _currentPage + 1) : null, icon: const Icon(Icons.chevron_right_rounded)),
        ],
      ),
    );
  }
}
