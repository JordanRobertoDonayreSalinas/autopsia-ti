import 'package:flutter/material.dart';
import '../../models/establecimiento.dart';
import '../../repositories/establecimiento_repository.dart';
import '../../ui/views/renipress_modal.dart';
import '../establecimiento_edit_screen.dart';

/// Pestaña "Establecimientos": listado paginado (10/página) con filtros de
/// búsqueda, provincia y distrito (cascada) — espejo de
/// resources/views/usuario/establecimientos/index.blade.php. El filtrado y
/// paginado se hace sobre el catálogo ya descargado localmente (igual que
/// los filtros geográficos del Dashboard), así funciona también offline.
class EstablecimientosTab extends StatefulWidget {
  const EstablecimientosTab({super.key});

  @override
  State<EstablecimientosTab> createState() => _EstablecimientosTabState();
}

class _EstablecimientosTabState extends State<EstablecimientosTab> {
  final _repo = EstablecimientoRepository();
  final _searchCtrl = TextEditingController();

  List<Establecimiento> _todos = [];
  bool _cargando = true;
  String _provinciaSel = '';
  String _distritoSel = '';
  int _pagina = 0;
  static const _porPagina = 10;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    final lista = await _repo.obtenerTodos();
    if (!mounted) return;
    setState(() {
      _todos = lista;
      _cargando = false;
    });
  }

  List<Establecimiento> get _filtrados {
    final term = _searchCtrl.text.trim().toLowerCase();
    return _todos.where((e) {
      final coincideTexto = term.isEmpty || e.nombre.toLowerCase().contains(term) || e.codigo.toLowerCase().contains(term) || e.responsable.toLowerCase().contains(term);
      final coincideProvincia = _provinciaSel.isEmpty || e.provincia == _provinciaSel;
      final coincideDistrito = _distritoSel.isEmpty || e.distrito == _distritoSel;
      return coincideTexto && coincideProvincia && coincideDistrito;
    }).toList();
  }

  List<String> get _provincias {
    final set = _todos.map((e) => e.provincia).where((p) => p.isNotEmpty).toSet().toList();
    set.sort();
    return set;
  }

  List<String> get _distritos {
    final base = _provinciaSel.isEmpty ? _todos : _todos.where((e) => e.provincia == _provinciaSel);
    final set = base.map((e) => e.distrito).where((d) => d.isNotEmpty).toSet().toList();
    set.sort();
    return set;
  }

  Future<void> _abrirEdicion(Establecimiento e) async {
    final guardado = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => EstablecimientoEditScreen(establecimiento: e)),
    );
    if (guardado == true) {
      await _cargar();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFF15803D), content: Text('Establecimiento actualizado con éxito.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final filtrados = _filtrados;
    final totalPaginas = (filtrados.length / _porPagina).ceil().clamp(1, 999999);
    final paginaActual = _pagina.clamp(0, totalPaginas - 1);
    final inicio = paginaActual * _porPagina;
    final fin = (inicio + _porPagina).clamp(0, filtrados.length);
    final visibles = filtrados.isEmpty ? <Establecimiento>[] : filtrados.sublist(inicio, fin);

    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Catálogo Maestro de Establecimientos', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                      SizedBox(height: 4),
                      Text('Gestione el registro único de establecimientos de salud.', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(color: const Color(0xFF0F172A), borderRadius: BorderRadius.circular(12)),
                  child: Column(
                    children: [
                      Text('${_todos.length}', style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                      const Text('REGISTRADOS', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                OutlinedButton.icon(
                  onPressed: () => showDialog(context: context, builder: (_) => const RenipressModal()),
                  style: OutlinedButton.styleFrom(side: const BorderSide(color: Color(0xFF4F46E5)), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                  icon: const Icon(Icons.verified_rounded, color: Color(0xFF4F46E5), size: 18),
                  label: const Text('Consultar RENIPRESS', style: TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 12)),
                ),
              ],
            ),
            const SizedBox(height: 20),

            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: [
                SizedBox(
                  width: 260,
                  child: TextField(
                    controller: _searchCtrl,
                    onChanged: (_) => setState(() => _pagina = 0),
                    decoration: InputDecoration(
                      hintText: 'Buscar por nombre, código o responsable',
                      prefixIcon: const Icon(Icons.search_rounded, size: 18),
                      isDense: true,
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
                SizedBox(
                  width: 200,
                  child: DropdownButtonFormField<String>(
                    initialValue: _provinciaSel.isEmpty ? null : _provinciaSel,
                    decoration: InputDecoration(labelText: 'Provincia', isDense: true, filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                    items: [const DropdownMenuItem(value: '', child: Text('TODAS')), ..._provincias.map((p) => DropdownMenuItem(value: p, child: Text(p)))],
                    onChanged: (v) => setState(() {
                      _provinciaSel = v ?? '';
                      _distritoSel = '';
                      _pagina = 0;
                    }),
                  ),
                ),
                SizedBox(
                  width: 200,
                  child: DropdownButtonFormField<String>(
                    initialValue: _distritoSel.isEmpty ? null : _distritoSel,
                    decoration: InputDecoration(labelText: 'Distrito', isDense: true, filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                    items: [const DropdownMenuItem(value: '', child: Text('TODOS')), ..._distritos.map((d) => DropdownMenuItem(value: d, child: Text(d)))],
                    onChanged: (v) => setState(() {
                      _distritoSel = v ?? '';
                      _pagina = 0;
                    }),
                  ),
                ),
                if (_searchCtrl.text.isNotEmpty || _provinciaSel.isNotEmpty || _distritoSel.isNotEmpty)
                  TextButton.icon(
                    onPressed: () => setState(() {
                      _searchCtrl.clear();
                      _provinciaSel = '';
                      _distritoSel = '';
                      _pagina = 0;
                    }),
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: const Text('Limpiar'),
                  ),
              ],
            ),
            const SizedBox(height: 20),

            if (_cargando)
              const Padding(padding: EdgeInsets.symmetric(vertical: 40), child: Center(child: CircularProgressIndicator()))
            else if (_todos.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(32),
                decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE2E8F0))),
                child: const Column(
                  children: [
                    Icon(Icons.cloud_off_rounded, size: 40, color: Color(0xFFCBD5E1)),
                    SizedBox(height: 10),
                    Text('No hay catálogo sincronizado todavía.', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                    SizedBox(height: 4),
                    Text('Conéctese a internet para descargar el catálogo real desde el servidor.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12), textAlign: TextAlign.center),
                  ],
                ),
              )
            else ...[
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowHeight: 40,
                  dataRowMinHeight: 56,
                  dataRowMaxHeight: 56,
                  horizontalMargin: 12,
                  columnSpacing: 28,
                  headingTextStyle: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.6),
                  columns: const [
                    DataColumn(label: Text('CÓDIGO')),
                    DataColumn(label: Text('NOMBRE')),
                    DataColumn(label: Text('CATEGORÍA')),
                    DataColumn(label: Text('UBICACIÓN')),
                    DataColumn(label: Text('RESPONSABLE')),
                    DataColumn(label: Text('TIPO DOC.')),
                    DataColumn(label: Text('N° DOCUMENTO')),
                    DataColumn(label: Text('ACCIONES')),
                  ],
                  rows: visibles.map((e) {
                    return DataRow(cells: [
                      DataCell(Text(e.codigo, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF2563EB), fontFamily: 'monospace', fontSize: 12))),
                      DataCell(SizedBox(width: 180, child: Text(e.nombre, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontSize: 12), overflow: TextOverflow.ellipsis))),
                      DataCell(Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(6)),
                        child: Text(e.categoria.isEmpty ? 'S/C' : e.categoria, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
                      )),
                      DataCell(Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(e.provincia, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
                          Text(e.distrito, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10)),
                        ],
                      )),
                      DataCell(SizedBox(width: 160, child: Text(e.responsable.isEmpty ? '-' : e.responsable, style: const TextStyle(fontSize: 12), overflow: TextOverflow.ellipsis))),
                      DataCell(
                        (e.tipoDocumento == null || e.tipoDocumento!.isEmpty)
                            ? const Text('-', style: TextStyle(color: Color(0xFF94A3B8)))
                            : Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                child: Text(e.tipoDocumento!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF1D4ED8))),
                              ),
                      ),
                      DataCell(Text(e.numeroDocumento ?? '-', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
                      DataCell(
                        OutlinedButton.icon(
                          onPressed: () => _abrirEdicion(e),
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Color(0xFFBFDBFE)),
                            backgroundColor: const Color(0xFFEFF6FF),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          ),
                          icon: const Icon(Icons.edit_rounded, size: 14, color: Color(0xFF1D4ED8)),
                          label: const Text('Editar', style: TextStyle(color: Color(0xFF1D4ED8), fontWeight: FontWeight.bold, fontSize: 11)),
                        ),
                      ),
                    ]);
                  }).toList(),
                ),
              ),
              if (filtrados.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 32),
                  child: Center(child: Text('No se encontraron establecimientos con ese criterio.', style: TextStyle(color: Color(0xFF94A3B8)))),
                ),
              if (totalPaginas > 1)
                Padding(
                  padding: const EdgeInsets.only(top: 16),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      IconButton(onPressed: paginaActual > 0 ? () => setState(() => _pagina--) : null, icon: const Icon(Icons.chevron_left_rounded)),
                      Text('Página ${paginaActual + 1} de $totalPaginas', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
                      IconButton(onPressed: paginaActual < totalPaginas - 1 ? () => setState(() => _pagina++) : null, icon: const Icon(Icons.chevron_right_rounded)),
                    ],
                  ),
                ),
            ],
          ],
        ),
      ),
    );
  }
}
