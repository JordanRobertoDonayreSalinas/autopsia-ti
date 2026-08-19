import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_monitoreo.dart';
import '../models/establecimiento.dart';
import '../repositories/acta_repository.dart';
import '../repositories/establecimiento_repository.dart';
import '../repositories/profesional_repository.dart';
import 'acta_detalle_screen.dart';

/// Formulario "Nueva Acta" — espejo de
/// resources/views/usuario/monitoreo/create.blade.php (MonitoreoController::store),
/// UNA sola pantalla como en el sistema real: el establecimiento se busca y
/// selecciona con un autocompletado embebido en la propia "Tarjeta 2: Datos
/// del Establecimiento" (no hay pantalla previa de selección en Laravel).
/// Exige además (todos obligatorios salvo donde se indica): fecha,
/// implementador (quién ejecuta, de una lista de usuarios), categoría y
/// responsable/jefe del establecimiento (editables, prellenados al elegir el
/// establecimiento), y un equipo de personal presente (mínimo 1 integrante)
/// — sin esto Laravel rechaza la creación (`equipo => required|array|min:1`).
class NuevaActaFormScreen extends StatefulWidget {
  final String userName;
  final List<Map<String, dynamic>> usuariosDisponibles;

  const NuevaActaFormScreen({
    super.key,
    required this.userName,
    this.usuariosDisponibles = const [],
  });

  @override
  State<NuevaActaFormScreen> createState() => _NuevaActaFormScreenState();
}

class _ParticipanteEquipoRow {
  final tipoDoc = TextEditingController(text: 'DNI');
  final doc = TextEditingController();
  final apellidoPaterno = TextEditingController();
  final apellidoMaterno = TextEditingController();
  final nombres = TextEditingController();
  final cargo = TextEditingController(text: 'IMPLEMENTADOR');
  String institucion = 'DIRESA';

  void dispose() {
    doc.dispose();
    apellidoPaterno.dispose();
    apellidoMaterno.dispose();
    nombres.dispose();
    cargo.dispose();
  }
}

const _institucionesEquipo = ['DIRESA', 'MINSA', 'U.E RED DE SALUD ICA', 'ESTABLECIMIENTO', 'OTRO'];

class _NuevaActaFormScreenState extends State<NuevaActaFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _actaRepo = ActaRepository();
  final _profesionalRepo = ProfesionalRepository();

  final _establecimientoRepo = EstablecimientoRepository();
  final _estSearchCtrl = TextEditingController();
  Establecimiento? _establecimiento;
  List<Establecimiento> _estResultados = [];
  bool _buscandoEst = false;

  late final TextEditingController _categoriaCtrl;
  late final TextEditingController _responsableCtrl;
  late String _implementador;
  DateTime _fecha = DateTime.now();

  final List<_ParticipanteEquipoRow> _equipo = [];
  final List<bool> _buscandoDni = [];

  String _pozoTierra = 'NO';
  final _pozoTierraCantCtrl = TextEditingController();
  final _pozoTierraOpCtrl = TextEditingController();
  final _pozoTierraInopCtrl = TextEditingController();
  String _panelSolar = 'NO';
  final _panelSolarCantCtrl = TextEditingController();
  final _panelSolarOpCtrl = TextEditingController();
  final _panelSolarInopCtrl = TextEditingController();

  String? _foto1Path;
  String? _foto2Path;
  bool _guardando = false;

  @override
  void initState() {
    super.initState();
    _categoriaCtrl = TextEditingController();
    _responsableCtrl = TextEditingController();
    _implementador = widget.userName.toUpperCase();
    _agregarFilaEquipo(precargarConUsuarioActual: true);
  }

  @override
  void dispose() {
    _estSearchCtrl.dispose();
    _categoriaCtrl.dispose();
    _responsableCtrl.dispose();
    _pozoTierraCantCtrl.dispose();
    _pozoTierraOpCtrl.dispose();
    _pozoTierraInopCtrl.dispose();
    _panelSolarCantCtrl.dispose();
    _panelSolarOpCtrl.dispose();
    _panelSolarInopCtrl.dispose();
    for (final p in _equipo) {
      p.dispose();
    }
    super.dispose();
  }

  void _agregarFilaEquipo({bool precargarConUsuarioActual = false}) {
    final row = _ParticipanteEquipoRow();
    if (precargarConUsuarioActual) {
      final partes = widget.userName.trim().split(' ');
      if (partes.length >= 2) {
        row.nombres.text = partes.last;
        row.apellidoPaterno.text = partes.first;
      } else {
        row.nombres.text = widget.userName;
      }
    }
    setState(() {
      _equipo.add(row);
      _buscandoDni.add(false);
    });
  }

  Future<void> _buscarDni(int index) async {
    final doc = _equipo[index].doc.text.trim();
    if (doc.length < 8) return;
    setState(() => _buscandoDni[index] = true);
    final prof = await _profesionalRepo.buscarPorDni(doc);
    if (!mounted) return;
    setState(() => _buscandoDni[index] = false);

    if (prof == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No se encontró en el catálogo local de profesionales.')));
      return;
    }
    setState(() {
      _equipo[index].apellidoPaterno.text = prof.apellidoPaterno ?? '';
      _equipo[index].apellidoMaterno.text = prof.apellidoMaterno ?? '';
      _equipo[index].nombres.text = prof.nombres ?? '';
    });
  }

  Future<void> _buscarEstablecimiento(String term) async {
    if (term.trim().isEmpty) {
      setState(() => _estResultados = []);
      return;
    }
    setState(() => _buscandoEst = true);
    final res = await _establecimientoRepo.buscar(term);
    if (!mounted) return;
    setState(() {
      _estResultados = res.take(8).toList();
      _buscandoEst = false;
    });
  }

  /// Espejo del callback `select:` del autocomplete real: al elegir un
  /// establecimiento se autorellenan categoría y responsable (editables
  /// después a mano).
  void _seleccionarEstablecimiento(Establecimiento e) {
    setState(() {
      _establecimiento = e;
      _estResultados = [];
      _estSearchCtrl.clear();
      _categoriaCtrl.text = e.categoria;
      _responsableCtrl.text = e.responsable;
    });
  }

  Future<void> _elegirFoto({required bool primera}) async {
    final res = await FilePicker.pickFiles(type: FileType.image);
    if (res.isEmpty || res.first.path == null) return;
    setState(() {
      if (primera) {
        _foto1Path = res.first.path;
      } else {
        _foto2Path = res.first.path;
      }
    });
  }

  Future<void> _guardar() async {
    final establecimiento = _establecimiento;
    if (establecimiento == null || establecimiento.id == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFFB91C1C), content: Text('Falta Establecimiento: busque y seleccione uno antes de guardar.')),
      );
      return;
    }
    if (!_formKey.currentState!.validate()) return;

    final participantesValidos = _equipo.where((p) => p.doc.text.trim().isNotEmpty && p.apellidoPaterno.text.trim().isNotEmpty && p.nombres.text.trim().isNotEmpty).toList();
    if (participantesValidos.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFFB91C1C), content: Text('Equipo Vacío: debe agregar al menos un integrante con documento, apellido paterno y nombres.')),
      );
      return;
    }

    setState(() => _guardando = true);

    final offlineId = 'ACTA-${DateTime.now().millisecondsSinceEpoch}';
    final acta = CabeceraMonitoreo(
      offlineId: offlineId,
      localCreatedAt: DateTime.now().toIso8601String(),
      establecimientoId: establecimiento.id!,
      fecha: '${_fecha.year.toString().padLeft(4, '0')}-${_fecha.month.toString().padLeft(2, '0')}-${_fecha.day.toString().padLeft(2, '0')}',
      responsable: _responsableCtrl.text.trim().toUpperCase(),
      implementador: _implementador.trim().toUpperCase(),
      categoriaCongelada: _categoriaCtrl.text.trim().toUpperCase(),
      pozoTierra: _pozoTierra,
      pozoTierraCantidad: _pozoTierra == 'SI' ? int.tryParse(_pozoTierraCantCtrl.text) : null,
      pozoTierraOperativos: _pozoTierra == 'SI' ? int.tryParse(_pozoTierraOpCtrl.text) : null,
      pozoTierraInoperativos: _pozoTierra == 'SI' ? int.tryParse(_pozoTierraInopCtrl.text) : null,
      panelSolar: _panelSolar,
      panelSolarCantidad: _panelSolar == 'SI' ? int.tryParse(_panelSolarCantCtrl.text) : null,
      panelSolarOperativos: _panelSolar == 'SI' ? int.tryParse(_panelSolarOpCtrl.text) : null,
      panelSolarInoperativos: _panelSolar == 'SI' ? int.tryParse(_panelSolarInopCtrl.text) : null,
      foto1: _foto1Path,
      foto2: _foto2Path,
    );

    final equipoMonitoreo = participantesValidos
        .map((p) => EquipoMonitoreo(
              actaOfflineId: offlineId,
              tipoDoc: p.tipoDoc.text.trim().isEmpty ? 'DNI' : p.tipoDoc.text.trim(),
              doc: p.doc.text.trim(),
              apellidoPaterno: p.apellidoPaterno.text.trim().toUpperCase(),
              apellidoMaterno: p.apellidoMaterno.text.trim().toUpperCase(),
              nombres: p.nombres.text.trim().toUpperCase(),
              cargo: p.cargo.text.trim().isEmpty ? 'MONITOR' : p.cargo.text.trim().toUpperCase(),
              institucion: p.institucion,
            ))
        .toList();

    await _actaRepo.guardarActaCompleta(acta, equipoMonitoreo: equipoMonitoreo);
    if (!mounted) return;
    setState(() => _guardando = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(backgroundColor: const Color(0xFF15803D), content: Text('Acta ($offlineId) guardada exitosamente en disco local para ${establecimiento.nombre}')),
    );

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => ActaDetalleScreen(offlineId: offlineId, establecimientoNombre: establecimiento.nombre)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final implementadoresDisponibles = <String>{
      widget.userName.toUpperCase(),
      ...widget.usuariosDisponibles.map((u) => (u['nombre_completo'] ?? '').toString().toUpperCase()).where((n) => n.isNotEmpty),
    }.toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Nueva Acta de Diagnóstico TI'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 900),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _seccion('Datos Generales', const Color(0xFF3B82F6), [
                  Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final res = await showDatePicker(context: context, initialDate: _fecha, firstDate: DateTime(2020), lastDate: DateTime(2100));
                          if (res != null) setState(() => _fecha = res);
                        },
                        icon: const Icon(Icons.calendar_today_rounded, size: 16),
                        label: Text('${_fecha.year}-${_fecha.month.toString().padLeft(2, '0')}-${_fecha.day.toString().padLeft(2, '0')}'),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      flex: 2,
                      child: DropdownButtonFormField<String>(
                        initialValue: implementadoresDisponibles.contains(_implementador) ? _implementador : implementadoresDisponibles.first,
                        decoration: const InputDecoration(labelText: 'Implementador Responsable', isDense: true),
                        items: implementadoresDisponibles.map((n) => DropdownMenuItem(value: n, child: Text(n, overflow: TextOverflow.ellipsis))).toList(),
                        onChanged: (v) => setState(() => _implementador = v ?? _implementador),
                      ),
                    ),
                  ]),
                ]),

                _seccion('Datos del Establecimiento', const Color(0xFF10B981), [
                  if (_establecimiento == null) ...[
                    TextField(
                      controller: _estSearchCtrl,
                      autofocus: true,
                      onChanged: _buscarEstablecimiento,
                      decoration: InputDecoration(
                        hintText: 'Ej: HOSPITAL REGIONAL, código, distrito…',
                        prefixIcon: _buscandoEst
                            ? const Padding(padding: EdgeInsets.all(12), child: SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2)))
                            : const Icon(Icons.search_rounded),
                        isDense: true,
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                      ),
                    ),
                    if (_estResultados.isNotEmpty)
                      Container(
                        margin: const EdgeInsets.only(top: 8),
                        decoration: BoxDecoration(border: Border.all(color: const Color(0xFFE2E8F0)), borderRadius: BorderRadius.circular(10)),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: _estResultados.map((e) {
                            return ListTile(
                              dense: true,
                              title: Text(e.nombre, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              subtitle: Text('${e.codigo} · ${e.distrito} - ${e.provincia}', style: const TextStyle(fontSize: 11)),
                              onTap: () => _seleccionarEstablecimiento(e),
                            );
                          }).toList(),
                        ),
                      ),
                  ] else
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(color: const Color(0xFFF0FDF4), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFF86EFAC))),
                      child: Row(children: [
                        const Icon(Icons.local_hospital_rounded, color: Color(0xFF15803D), size: 20),
                        const SizedBox(width: 10),
                        Expanded(child: Text('${_establecimiento!.nombre} (${_establecimiento!.codigo})', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontSize: 13))),
                        TextButton(
                          onPressed: () => setState(() => _establecimiento = null),
                          child: const Text('Cambiar'),
                        ),
                      ]),
                    ),
                  const SizedBox(height: 14),
                  Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Expanded(
                      child: TextFormField(
                        controller: _categoriaCtrl,
                        decoration: const InputDecoration(labelText: 'Categoría', isDense: true),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      flex: 2,
                      child: TextFormField(
                        controller: _responsableCtrl,
                        decoration: const InputDecoration(labelText: 'Jefe del Establecimiento (Responsable)', isDense: true),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                      ),
                    ),
                  ]),
                ]),

                _seccion('Equipo Presente en la Visita', const Color(0xFF6366F1), [
                  ..._equipo.asMap().entries.map((e) => _filaEquipo(e.key, e.value)),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => _agregarFilaEquipo(),
                    icon: const Icon(Icons.person_add_alt_1_rounded, size: 16),
                    label: const Text('Agregar integrante'),
                  ),
                ]),

                _seccion('Infraestructura Eléctrica', const Color(0xFFF59E0B), [
                  _campoInstalacion('Pozo a tierra', _pozoTierra, _pozoTierraCantCtrl, _pozoTierraOpCtrl, _pozoTierraInopCtrl, (v) => setState(() => _pozoTierra = v)),
                  const SizedBox(height: 12),
                  _campoInstalacion('Panel solar', _panelSolar, _panelSolarCantCtrl, _panelSolarOpCtrl, _panelSolarInopCtrl, (v) => setState(() => _panelSolar = v)),
                ]),

                _seccion('Evidencia Fotográfica (máx. 2)', const Color(0xFF06B6D4), [
                  Row(children: [
                    Expanded(child: _fotoSlot(_foto1Path, () => _elegirFoto(primera: true), () => setState(() => _foto1Path = null))),
                    const SizedBox(width: 12),
                    Expanded(child: _fotoSlot(_foto2Path, () => _elegirFoto(primera: false), () => setState(() => _foto2Path = null))),
                  ]),
                ]),

                const SizedBox(height: 8),
                const Text(
                  'Los consultorios (Triaje, Farmacia, etc.) y el módulo de RR.HH. se agregan después de guardar, desde el detalle de la acta.',
                  style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                ),
                const SizedBox(height: 24),

                Align(
                  alignment: Alignment.centerRight,
                  child: ElevatedButton.icon(
                    onPressed: _guardando ? null : _guardar,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: _guardando
                        ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.save_rounded, size: 18),
                    label: const Text('Guardar Acta (Offline)', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _seccion(String titulo, Color color, List<Widget> children) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: const Color(0xFFE2E8F0))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Container(width: 4, height: 18, decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4))),
            const SizedBox(width: 8),
            Text(titulo.toUpperCase(), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B), letterSpacing: 0.4)),
          ]),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _filaEquipo(int index, _ParticipanteEquipoRow row) {
    final buscando = _buscandoDni[index];
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(10)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Text('N° ${index + 1}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF4F46E5))),
            const Spacer(),
            if (_equipo.length > 1)
              IconButton(
                icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFB91C1C)),
                onPressed: () => setState(() {
                  row.dispose();
                  _equipo.removeAt(index);
                  _buscandoDni.removeAt(index);
                }),
              ),
          ]),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            crossAxisAlignment: WrapCrossAlignment.end,
            children: [
              SizedBox(
                width: 160,
                child: Row(children: [
                  Expanded(
                    child: TextFormField(
                      controller: row.doc,
                      decoration: const InputDecoration(labelText: 'Documento', isDense: true, filled: true, fillColor: Colors.white),
                      keyboardType: TextInputType.number,
                    ),
                  ),
                  IconButton(
                    tooltip: 'Buscar en catálogo local',
                    icon: buscando ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.search_rounded, size: 18),
                    onPressed: buscando ? null : () => _buscarDni(index),
                  ),
                ]),
              ),
              _campoAncho(row.apellidoPaterno, 'Apellido Paterno', requerido: true),
              _campoAncho(row.apellidoMaterno, 'Apellido Materno'),
              _campoAncho(row.nombres, 'Nombres', requerido: true),
              _campoAncho(row.cargo, 'Cargo'),
              SizedBox(
                width: 190,
                child: DropdownButtonFormField<String>(
                  initialValue: row.institucion,
                  decoration: const InputDecoration(labelText: 'Institución', isDense: true, filled: true, fillColor: Colors.white),
                  items: _institucionesEquipo.map((i) => DropdownMenuItem(value: i, child: Text(i, overflow: TextOverflow.ellipsis))).toList(),
                  onChanged: (v) => setState(() => row.institucion = v ?? row.institucion),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _campoAncho(TextEditingController ctrl, String label, {bool requerido = false}) {
    return SizedBox(
      width: 170,
      child: TextFormField(
        controller: ctrl,
        decoration: InputDecoration(labelText: label, isDense: true, filled: true, fillColor: Colors.white),
        validator: requerido ? (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null : null,
      ),
    );
  }

  Widget _campoInstalacion(String label, String valor, TextEditingController cantCtrl, TextEditingController opCtrl, TextEditingController inopCtrl, ValueChanged<String> onChanged) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(children: [
          Expanded(child: Text(label, style: const TextStyle(fontSize: 13, color: Color(0xFF334155)))),
          ChoiceChip(label: const Text('SI'), selected: valor == 'SI', onSelected: (_) => onChanged('SI')),
          const SizedBox(width: 6),
          ChoiceChip(label: const Text('NO'), selected: valor == 'NO', onSelected: (_) => onChanged('NO')),
        ]),
        if (valor == 'SI') ...[
          const SizedBox(height: 8),
          Row(children: [
            Expanded(child: TextField(controller: cantCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Cantidad', isDense: true, filled: true, fillColor: Colors.white))),
            const SizedBox(width: 8),
            Expanded(child: TextField(controller: opCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Operativos', isDense: true, filled: true, fillColor: Colors.white))),
            const SizedBox(width: 8),
            Expanded(child: TextField(controller: inopCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Inoperativos', isDense: true, filled: true, fillColor: Colors.white))),
          ]),
        ],
      ],
    );
  }

  Widget _fotoSlot(String? path, VoidCallback onPick, VoidCallback onRemove) {
    return Container(
      height: 120,
      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFCBD5E1))),
      child: path == null
          ? InkWell(
              onTap: onPick,
              borderRadius: BorderRadius.circular(12),
              child: const Center(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.add_a_photo_outlined, color: Color(0xFF94A3B8)),
                  SizedBox(height: 6),
                  Text('Agregar foto', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                ]),
              ),
            )
          : Stack(fit: StackFit.expand, children: [
              ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.file(File(path), fit: BoxFit.cover)),
              Positioned(
                top: 4,
                right: 4,
                child: InkWell(onTap: onRemove, child: const CircleAvatar(radius: 12, backgroundColor: Colors.black54, child: Icon(Icons.close_rounded, size: 14, color: Colors.white))),
              ),
            ]),
    );
  }
}
