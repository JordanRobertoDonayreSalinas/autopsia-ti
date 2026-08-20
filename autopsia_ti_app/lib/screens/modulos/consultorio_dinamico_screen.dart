import 'dart:convert';
import 'package:flutter/material.dart';
import '../../models/equipo_computo.dart';
import '../../repositories/acta_repository.dart';

/// Formulario genérico de "consultorio dinámico" — espejo de
/// MonitoreoModuloGenericController::storeConsultorio y de la vista
/// resources/views/usuario/monitoreo/modulos/consultorio_dinamico.blade.php.
/// El auditor agrega estos consultorios con nombre libre (Triaje, Farmacia,
/// etc.) durante la visita; todos usan este mismo formulario. Todo el
/// contenido se guarda plano en mon_monitoreo_modulos.contenido, salvo los
/// equipos de cómputo, que van en la tabla separada mon_equipos_computo.
class ConsultorioDinamicoScreen extends StatefulWidget {
  final String actaOfflineId;
  final String moduloNombre; // slug
  final String establecimientoNombre;

  const ConsultorioDinamicoScreen({
    super.key,
    required this.actaOfflineId,
    required this.moduloNombre,
    required this.establecimientoNombre,
  });

  @override
  State<ConsultorioDinamicoScreen> createState() => _ConsultorioDinamicoScreenState();
}

class _ConsultorioDinamicoScreenState extends State<ConsultorioDinamicoScreen> {
  final ActaRepository _actaRepo = ActaRepository();

  final _tituloCtrl = TextEditingController();
  final _servicioCtrl = TextEditingController();
  final _fechaCtrl = TextEditingController();
  final _pisoCtrl = TextEditingController();
  final _cantidadPuntosRedCtrl = TextEditingController();
  final _operadorOtroCtrl = TextEditingController();
  final _velocidadDescargaCtrl = TextEditingController();
  final _velocidadSubidaCtrl = TextEditingController();
  final _observacionesCtrl = TextEditingController();

  String _turno = 'MAÑANA';
  String _tipoConsultorio = 'FISICO';
  String _cuentaElectricidad = 'NO';
  String _cuentaPuntoRed = 'NO';
  String _tipoConectividad = 'SIN CONECTIVIDAD';
  String _wifiFuente = 'ESTABLECIMIENTO';
  String _operadorServicio = 'WOW';
  String _velocidadDescargaUnidad = 'Mbps';
  String _velocidadSubidaUnidad = 'Mbps';

  List<EquipoComputo> _equipos = [];
  bool _isLoading = true;
  bool _isSaving = false;

  static const _operadores = [
    'WOW', 'MOVISTAR', 'ENTEL', 'CLARO', 'BITEL', 'FIBERPRO', 'NUBYX', 'WIN',
    'TICTEL', 'GILAT', 'ALTINET', 'DELAFIBER', 'COMPUIVAN', 'STARLINK', 'OTROS',
  ];

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    final modulo = await _actaRepo.obtenerModulos(widget.actaOfflineId);
    final match = modulo.where((m) => m.moduloNombre == widget.moduloNombre);
    final equipos = await _actaRepo.obtenerEquiposDeModulo(widget.actaOfflineId, widget.moduloNombre);

    if (match.isNotEmpty) {
      final data = jsonDecode(match.first.contenido) as Map<String, dynamic>;
      _tituloCtrl.text = data['titulo_consultorio'] ?? '';
      _servicioCtrl.text = data['servicio_asociado'] ?? '';
      _fechaCtrl.text = data['fecha'] ?? DateTime.now().toString().split(' ')[0];
      _pisoCtrl.text = (data['piso'] ?? '').toString();
      _cantidadPuntosRedCtrl.text = (data['cantidad_puntos_red'] ?? '').toString();
      _operadorOtroCtrl.text = data['operador_otro'] ?? '';
      _velocidadDescargaCtrl.text = (data['velocidad_descarga'] ?? '').toString();
      _velocidadSubidaCtrl.text = (data['velocidad_subida'] ?? '').toString();
      _observacionesCtrl.text = data['observaciones'] ?? '';
      _turno = data['turno'] ?? 'MAÑANA';
      _tipoConsultorio = data['tipo_consultorio'] ?? 'FISICO';
      _cuentaElectricidad = data['cuenta_electricidad'] ?? 'NO';
      _cuentaPuntoRed = data['cuenta_punto_red'] ?? 'NO';
      _tipoConectividad = data['tipo_conectividad'] ?? 'SIN CONECTIVIDAD';
      _wifiFuente = data['wifi_fuente'] ?? 'ESTABLECIMIENTO';
      _operadorServicio = data['operador_servicio'] ?? 'WOW';
      _velocidadDescargaUnidad = data['velocidad_descarga_unidad'] ?? 'Mbps';
      _velocidadSubidaUnidad = data['velocidad_subida_unidad'] ?? 'Mbps';
    } else {
      _fechaCtrl.text = DateTime.now().toString().split(' ')[0];
    }

    setState(() {
      _equipos = equipos;
      _isLoading = false;
    });
  }

  Future<void> _persistir() async {
    setState(() => _isSaving = true);
    final contenido = jsonEncode({
      'titulo_consultorio': _tituloCtrl.text.trim().toUpperCase(),
      'servicio_asociado': _servicioCtrl.text.trim(),
      'fecha': _fechaCtrl.text.trim(),
      'turno': _turno,
      'tipo_consultorio': _tipoConsultorio,
      'piso': int.tryParse(_pisoCtrl.text),
      'cuenta_electricidad': _cuentaElectricidad,
      'cuenta_punto_red': _cuentaPuntoRed,
      'cantidad_puntos_red': _cuentaPuntoRed == 'SI' ? int.tryParse(_cantidadPuntosRedCtrl.text) : null,
      'tipo_conectividad': _tipoConectividad,
      'wifi_fuente': _tipoConectividad == 'WIFI' ? _wifiFuente : null,
      'operador_servicio': _tipoConectividad != 'SIN CONECTIVIDAD'
          ? (_operadorServicio == 'OTROS' ? _operadorOtroCtrl.text.trim() : _operadorServicio)
          : null,
      'velocidad_descarga': _tipoConectividad != 'SIN CONECTIVIDAD' ? _velocidadDescargaCtrl.text.trim() : null,
      'velocidad_descarga_unidad': _velocidadDescargaUnidad,
      'velocidad_subida': _tipoConectividad != 'SIN CONECTIVIDAD' ? _velocidadSubidaCtrl.text.trim() : null,
      'velocidad_subida_unidad': _velocidadSubidaUnidad,
      'observaciones': _observacionesCtrl.text.trim(),
    });

    await _actaRepo.guardarConsultorio(widget.actaOfflineId, widget.moduloNombre, contenido);
    if (mounted) setState(() => _isSaving = false);
  }

  Future<void> _abrirFormularioEquipo({EquipoComputo? existente}) async {
    final resultado = await showDialog<EquipoComputo>(
      context: context,
      builder: (_) => _EquipoFormDialog(existente: existente),
    );
    if (resultado == null) return;

    final equipoCompleto = EquipoComputo(
      actaOfflineId: widget.actaOfflineId,
      modulo: widget.moduloNombre,
      descripcion: resultado.descripcion,
      cantidad: resultado.cantidad,
      estado: resultado.estado,
      propio: resultado.propio,
      nroSerie: resultado.nroSerie,
      observacion: resultado.observacion,
    );

    setState(() {
      if (existente != null) {
        final idx = _equipos.indexOf(existente);
        _equipos[idx] = equipoCompleto;
      } else {
        _equipos.add(equipoCompleto);
      }
    });
    await _actaRepo.reemplazarEquiposDeModulo(widget.actaOfflineId, widget.moduloNombre, _equipos);
  }

  Future<void> _eliminarEquipo(EquipoComputo eq) async {
    setState(() => _equipos.remove(eq));
    await _actaRepo.reemplazarEquiposDeModulo(widget.actaOfflineId, widget.moduloNombre, _equipos);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: Text(
          _tituloCtrl.text.isEmpty ? widget.establecimientoNombre : _tituloCtrl.text,
          overflow: TextOverflow.ellipsis,
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          if (_isSaving)
            const Padding(
              padding: EdgeInsets.all(16),
              child: SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _seccion('Datos Generales', [
                  _campoTexto('Título del consultorio', _tituloCtrl, requerido: true, onDone: _persistir),
                  _campoTexto('Servicio asociado (opcional)', _servicioCtrl, onDone: _persistir),
                  _campoTexto('Fecha (AAAA-MM-DD)', _fechaCtrl, onDone: _persistir),
                  _pillGroup('Turno', ['MAÑANA', 'TARDE'], _turno, (v) => setState(() => _turno = v)),
                  _dropdown('Tipo de consultorio', _tipoConsultorio, const ['FISICO', 'FUNCIONAL'], (v) => setState(() => _tipoConsultorio = v!)),
                  _campoTexto('Piso', _pisoCtrl, teclado: TextInputType.number, onDone: _persistir),
                  _pillGroup('¿Cuenta con electricidad?', ['SI', 'NO'], _cuentaElectricidad, (v) => setState(() => _cuentaElectricidad = v)),
                  _pillGroup('¿Cuenta con punto de red?', ['SI', 'NO'], _cuentaPuntoRed, (v) => setState(() => _cuentaPuntoRed = v)),
                  if (_cuentaPuntoRed == 'SI') _campoTexto('Cantidad de puntos de red', _cantidadPuntosRedCtrl, teclado: TextInputType.number, onDone: _persistir),
                ]),
                const SizedBox(height: 16),
                _seccionEquipos(),
                const SizedBox(height: 16),
                _seccion('Tipo de Conectividad', [
                  _dropdown('Conectividad', _tipoConectividad, const ['WIFI', 'CABLEADO', 'SIN CONECTIVIDAD'], (v) => setState(() => _tipoConectividad = v!)),
                  if (_tipoConectividad == 'WIFI')
                    _pillGroup('Fuente de wifi', ['ESTABLECIMIENTO', 'PERSONAL'], _wifiFuente, (v) => setState(() => _wifiFuente = v)),
                  if (_tipoConectividad != 'SIN CONECTIVIDAD') ...[
                    _dropdown('Operador de servicio', _operadorServicio, _operadores, (v) => setState(() => _operadorServicio = v!)),
                    if (_operadorServicio == 'OTROS') _campoTexto('¿Cuál operador?', _operadorOtroCtrl, onDone: _persistir),
                    Row(children: [
                      Expanded(child: _campoTexto('Velocidad de descarga', _velocidadDescargaCtrl, teclado: TextInputType.number, onDone: _persistir)),
                      const SizedBox(width: 8),
                      SizedBox(width: 100, child: _dropdown('Unidad', _velocidadDescargaUnidad, const ['Mbps', 'Gbps', 'Kbps'], (v) => setState(() => _velocidadDescargaUnidad = v!))),
                    ]),
                    Row(children: [
                      Expanded(child: _campoTexto('Velocidad de subida', _velocidadSubidaCtrl, teclado: TextInputType.number, onDone: _persistir)),
                      const SizedBox(width: 8),
                      SizedBox(width: 100, child: _dropdown('Unidad', _velocidadSubidaUnidad, const ['Mbps', 'Gbps', 'Kbps'], (v) => setState(() => _velocidadSubidaUnidad = v!))),
                    ]),
                  ],
                ]),
                const SizedBox(height: 16),
                _seccion('Observaciones', [
                  TextField(
                    controller: _observacionesCtrl,
                    maxLines: 3,
                    onEditingComplete: _persistir,
                    decoration: InputDecoration(
                      hintText: 'Observaciones del consultorio...',
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ]),
                const SizedBox(height: 20),
                ElevatedButton.icon(
                  onPressed: _persistir,
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white, minimumSize: const Size.fromHeight(48)),
                  icon: const Icon(Icons.save_rounded),
                  label: const Text('Guardar consultorio'),
                ),
                const SizedBox(height: 40),
              ],
            ),
    );
  }

  Widget _seccion(String titulo, List<Widget> children) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(titulo, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
            const SizedBox(height: 12),
            ...children.map((w) => Padding(padding: const EdgeInsets.only(bottom: 12), child: w)),
          ],
        ),
      ),
    );
  }

  Widget _seccionEquipos() {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [
              const Expanded(child: Text('Equipos de Cómputo e Impresora', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)))),
              TextButton.icon(onPressed: () => _abrirFormularioEquipo(), icon: const Icon(Icons.add, size: 18), label: const Text('Agregar')),
            ]),
            if (_equipos.isEmpty)
              const Padding(padding: EdgeInsets.symmetric(vertical: 8), child: Text('Sin equipos registrados en este consultorio.', style: TextStyle(color: Color(0xFF94A3B8))))
            else
              ..._equipos.map((eq) => ListTile(
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                    leading: const Icon(Icons.computer_rounded, color: Color(0xFF4F46E5)),
                    title: Text(eq.descripcion, style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text('${eq.estado} · ${eq.propio ?? '-'} · Serie: ${eq.nroSerie ?? '-'}'),
                    trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                      IconButton(icon: const Icon(Icons.edit_outlined, size: 20), onPressed: () => _abrirFormularioEquipo(existente: eq)),
                      IconButton(icon: const Icon(Icons.delete_outline_rounded, size: 20, color: Color(0xFFEF4444)), onPressed: () => _eliminarEquipo(eq)),
                    ]),
                  )),
          ],
        ),
      ),
    );
  }

  Widget _campoTexto(String label, TextEditingController ctrl, {bool requerido = false, TextInputType? teclado, VoidCallback? onDone}) {
    return TextField(
      controller: ctrl,
      keyboardType: teclado,
      onEditingComplete: onDone,
      decoration: InputDecoration(
        labelText: requerido ? '$label *' : label,
        filled: true,
        fillColor: const Color(0xFFF8FAFC),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  Widget _dropdown(String label, String value, List<String> items, ValueChanged<String?> onChanged) {
    return DropdownButtonFormField<String>(
      initialValue: value,
      isExpanded: true,
      decoration: InputDecoration(
        labelText: label,
        filled: true,
        fillColor: const Color(0xFFF8FAFC),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
      items: items.map((e) => DropdownMenuItem(value: e, child: Text(e, overflow: TextOverflow.ellipsis))).toList(),
      onChanged: (v) {
        onChanged(v);
        _persistir();
      },
    );
  }

  Widget _pillGroup(String label, List<String> opciones, String valor, ValueChanged<String> onChanged) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B))),
        const SizedBox(height: 6),
        Wrap(
          spacing: 8,
          children: opciones
              .map((o) => ChoiceChip(
                    label: Text(o),
                    selected: valor == o,
                    onSelected: (_) {
                      onChanged(o);
                      _persistir();
                    },
                  ))
              .toList(),
        ),
      ],
    );
  }
}

class _EquipoFormDialog extends StatefulWidget {
  final EquipoComputo? existente;
  const _EquipoFormDialog({this.existente});

  @override
  State<_EquipoFormDialog> createState() => _EquipoFormDialogState();
}

class _EquipoFormDialogState extends State<_EquipoFormDialog> {
  final _descripcionCtrl = TextEditingController();
  final _cantidadCtrl = TextEditingController(text: '1');
  final _nroSerieCtrl = TextEditingController();
  final _observacionCtrl = TextEditingController();
  String _estado = 'OPERATIVO';
  String _propio = 'EXCLUSIVO';

  @override
  void initState() {
    super.initState();
    final eq = widget.existente;
    if (eq != null) {
      _descripcionCtrl.text = eq.descripcion;
      _cantidadCtrl.text = eq.cantidad.toString();
      _nroSerieCtrl.text = eq.nroSerie ?? '';
      _observacionCtrl.text = eq.observacion ?? '';
      _estado = eq.estado;
      _propio = eq.propio ?? 'EXCLUSIVO';
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Text(widget.existente == null ? 'Agregar Equipo' : 'Editar Equipo'),
      content: SizedBox(
        width: 420,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: _descripcionCtrl,
                decoration: InputDecoration(labelText: 'Descripción (ej. CPU, LAPTOP, IMPRESORA...)', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _cantidadCtrl,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(labelText: 'Cantidad', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _estado,
                decoration: InputDecoration(labelText: 'Estado', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                items: const ['OPERATIVO', 'REGULAR', 'INOPERATIVO'].map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                onChanged: (v) => setState(() => _estado = v!),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _propio,
                decoration: InputDecoration(labelText: 'Propiedad', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                items: const ['EXCLUSIVO', 'COMPARTIDO', 'PERSONAL', 'ALQUILADO'].map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                onChanged: (v) => setState(() => _propio = v!),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _nroSerieCtrl,
                decoration: InputDecoration(labelText: 'N° Serie', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _observacionCtrl,
                decoration: InputDecoration(labelText: 'Observación', filled: true, fillColor: const Color(0xFFF8FAFC), border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancelar')),
        ElevatedButton(
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
          onPressed: () {
            if (_descripcionCtrl.text.trim().isEmpty) return;
            Navigator.pop(
              context,
              EquipoComputo(
                actaOfflineId: '', // se completa desde la pantalla llamante al reemplazar la lista
                descripcion: _descripcionCtrl.text.trim().toUpperCase(),
                modulo: '', // idem
                cantidad: int.tryParse(_cantidadCtrl.text) ?? 1,
                estado: _estado,
                propio: _propio,
                nroSerie: _nroSerieCtrl.text.trim().isEmpty ? null : _nroSerieCtrl.text.trim(),
                observacion: _observacionCtrl.text.trim().isEmpty ? null : _observacionCtrl.text.trim(),
              ),
            );
          },
          child: const Text('Guardar'),
        ),
      ],
    );
  }
}
