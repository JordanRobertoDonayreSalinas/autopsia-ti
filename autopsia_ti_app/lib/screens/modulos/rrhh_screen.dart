import 'dart:convert';
import 'package:flutter/material.dart';
import '../../models/trabajador_rrhh.dart';
import '../../repositories/acta_repository.dart';

/// Módulo "01. Recursos Humanos" — espejo de RecursosHumanosController /
/// resources/views/usuario/monitoreo/modulos/rrhh.blade.php. Guarda un solo
/// registro de mon_monitoreo_modulos (modulo_nombre='rrhh') cuyo `contenido`
/// es { trabajadores: [...], observaciones, total_trabajadores, fecha_actualizacion }.
///
/// Nota: a diferencia del formulario web, esta primera versión no captura
/// las 2 fotos de evidencia (foto_1/foto_2) — requiere agregar cámara/galería
/// como dependencia nueva, fuera del alcance de este corte.
class RRHHScreen extends StatefulWidget {
  final String actaOfflineId;
  final String establecimientoNombre;

  const RRHHScreen({super.key, required this.actaOfflineId, required this.establecimientoNombre});

  @override
  State<RRHHScreen> createState() => _RRHHScreenState();
}

class _RRHHScreenState extends State<RRHHScreen> {
  final ActaRepository _actaRepo = ActaRepository();
  final _obsCtrl = TextEditingController();
  List<TrabajadorRRHH> _trabajadores = [];
  bool _isLoading = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    final modulo = await _actaRepo.obtenerModuloRRHH(widget.actaOfflineId);
    if (modulo != null) {
      final data = jsonDecode(modulo.contenido) as Map<String, dynamic>;
      final lista = (data['trabajadores'] as List? ?? [])
          .map((t) => TrabajadorRRHH.fromJson(Map<String, dynamic>.from(t)))
          .toList();
      setState(() {
        _trabajadores = lista;
        _obsCtrl.text = data['observaciones'] ?? '';
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _persistir() async {
    setState(() => _isSaving = true);
    final contenido = jsonEncode({
      'trabajadores': _trabajadores.map((t) => t.toJson()).toList(),
      'observaciones': _obsCtrl.text.trim(),
      'total_trabajadores': _trabajadores.length,
      'fecha_actualizacion': DateTime.now().toIso8601String(),
      'foto_1': null,
      'foto_2': null,
    });
    await _actaRepo.guardarModuloRRHH(widget.actaOfflineId, contenido);
    if (mounted) setState(() => _isSaving = false);
  }

  Future<void> _abrirFormularioTrabajador({TrabajadorRRHH? existente}) async {
    final resultado = await showDialog<TrabajadorRRHH>(
      context: context,
      builder: (_) => _TrabajadorFormDialog(existente: existente),
    );
    if (resultado == null) return;

    setState(() {
      final idx = _trabajadores.indexWhere((t) => t.id == resultado.id);
      if (idx != -1) {
        _trabajadores[idx] = resultado;
      } else {
        _trabajadores.add(resultado);
      }
    });
    await _persistir();
  }

  Future<void> _eliminarTrabajador(TrabajadorRRHH t) async {
    setState(() => _trabajadores.removeWhere((e) => e.id == t.id));
    await _persistir();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: Text('RR.HH. · ${widget.establecimientoNombre}', overflow: TextOverflow.ellipsis),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          if (_isSaving) const Padding(padding: EdgeInsets.all(16), child: SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _abrirFormularioTrabajador(),
        backgroundColor: const Color(0xFF4F46E5),
        icon: const Icon(Icons.person_add_alt_1_rounded),
        label: const Text('Agregar trabajador'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Text('Personal de Salud (${_trabajadores.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                const SizedBox(height: 12),
                if (_trabajadores.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(28),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: const Color(0xFFE2E8F0))),
                    child: const Column(
                      children: [
                        Icon(Icons.people_outline_rounded, size: 40, color: Color(0xFFCBD5E1)),
                        SizedBox(height: 10),
                        Text('Aún no se registró personal de salud en este establecimiento.', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w500), textAlign: TextAlign.center),
                      ],
                    ),
                  )
                else
                  ..._trabajadores.map((t) => Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: Color(0xFFE2E8F0))),
                        child: ListTile(
                          leading: CircleAvatar(backgroundColor: const Color(0xFF3B82F6), child: Text(t.nombres.isNotEmpty ? t.nombres[0] : '?', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                          title: Text(t.nombreCompleto, style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('${t.profesion} · ${t.servicio} · ${t.tipoDoc} ${t.doc}'),
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(icon: const Icon(Icons.edit_outlined, size: 20), onPressed: () => _abrirFormularioTrabajador(existente: t)),
                              IconButton(icon: const Icon(Icons.delete_outline_rounded, size: 20, color: Color(0xFFEF4444)), onPressed: () => _eliminarTrabajador(t)),
                            ],
                          ),
                        ),
                      )),
                const SizedBox(height: 20),
                const Text('Observaciones', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
                const SizedBox(height: 6),
                TextField(
                  controller: _obsCtrl,
                  maxLines: 3,
                  onEditingComplete: _persistir,
                  decoration: InputDecoration(
                    hintText: 'Observaciones sobre el personal de salud del establecimiento...',
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 90),
              ],
            ),
    );
  }
}

class _TrabajadorFormDialog extends StatefulWidget {
  final TrabajadorRRHH? existente;
  const _TrabajadorFormDialog({this.existente});

  @override
  State<_TrabajadorFormDialog> createState() => _TrabajadorFormDialogState();
}

class _TrabajadorFormDialogState extends State<_TrabajadorFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late String _servicio;
  late String _tipoDoc;
  late String _profesion;
  late String _esSerums;
  String? _periodoSerums;
  final _docCtrl = TextEditingController();
  final _apPaternoCtrl = TextEditingController();
  final _apMaternoCtrl = TextEditingController();
  final _nombresCtrl = TextEditingController();
  final _colegioCtrl = TextEditingController();
  final _colegiaturaCtrl = TextEditingController();
  final _correoCtrl = TextEditingController();
  final _celularCtrl = TextEditingController();
  final _rneCtrl = TextEditingController();

  List<String> get _periodosDisponibles {
    final now = DateTime.now();
    final y = now.year;
    final m = now.month;
    if (m < 10) {
      return ['${y - 1}-1', '${y - 1}-2', '$y-1'];
    }
    return ['${y - 1}-2', '$y-1', '$y-2'];
  }

  @override
  void initState() {
    super.initState();
    final t = widget.existente;
    _servicio = t?.servicio ?? CatalogosRRHH.servicios.first;
    _tipoDoc = t?.tipoDoc ?? 'DNI';
    _profesion = t?.profesion ?? CatalogosRRHH.profesiones.first;
    _esSerums = t?.esSerums ?? 'NO';
    _periodoSerums = t?.periodoSerums;
    _docCtrl.text = t?.doc ?? '';
    _apPaternoCtrl.text = t?.apellidoPaterno ?? '';
    _apMaternoCtrl.text = t?.apellidoMaterno ?? '';
    _nombresCtrl.text = t?.nombres ?? '';
    _colegioCtrl.text = t?.colegioProfesional ?? '';
    _colegiaturaCtrl.text = t?.colegiatura ?? '';
    _correoCtrl.text = t?.correo ?? '';
    _celularCtrl.text = t?.celular ?? '';
    _rneCtrl.text = t?.rne ?? '';
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Text(widget.existente == null ? 'Agregar Trabajador' : 'Editar Trabajador'),
      content: SizedBox(
        width: 480,
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _dropdown('Servicio', _servicio, CatalogosRRHH.servicios, (v) => setState(() => _servicio = v!)),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(flex: 1, child: _dropdown('Tipo Doc.', _tipoDoc, CatalogosRRHH.tiposDoc, (v) => setState(() => _tipoDoc = v!))),
                  const SizedBox(width: 8),
                  Expanded(flex: 2, child: _textField('Documento', _docCtrl, requerido: true)),
                ]),
                const SizedBox(height: 12),
                _textField('Apellido Paterno', _apPaternoCtrl, requerido: true),
                const SizedBox(height: 12),
                _textField('Apellido Materno', _apMaternoCtrl),
                const SizedBox(height: 12),
                _textField('Nombres', _nombresCtrl, requerido: true),
                const SizedBox(height: 12),
                _dropdown('Profesión', _profesion, CatalogosRRHH.profesiones, (v) => setState(() => _profesion = v!)),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: _textField('Colegio Profesional', _colegioCtrl)),
                  const SizedBox(width: 8),
                  Expanded(child: _textField('N° Colegiatura', _colegiaturaCtrl)),
                ]),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: _textField('Correo', _correoCtrl)),
                  const SizedBox(width: 8),
                  Expanded(child: _textField('Celular', _celularCtrl)),
                ]),
                const SizedBox(height: 12),
                _textField('RNE (opcional)', _rneCtrl),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Text('¿Es SERUMS?', style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(width: 12),
                    ChoiceChip(label: const Text('SI'), selected: _esSerums == 'SI', onSelected: (_) => setState(() => _esSerums = 'SI')),
                    const SizedBox(width: 6),
                    ChoiceChip(label: const Text('NO'), selected: _esSerums == 'NO', onSelected: (_) => setState(() => _esSerums = 'NO')),
                  ],
                ),
                if (_esSerums == 'SI') ...[
                  const SizedBox(height: 12),
                  _dropdown('Periodo SERUMS', _periodoSerums ?? _periodosDisponibles.first, _periodosDisponibles, (v) => setState(() => _periodoSerums = v)),
                ],
              ],
            ),
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancelar')),
        ElevatedButton(
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
          onPressed: () {
            if (!_formKey.currentState!.validate()) return;
            final t = TrabajadorRRHH(
              id: widget.existente?.id ?? 'tr_${DateTime.now().millisecondsSinceEpoch}',
              servicio: _servicio,
              tipoDoc: _tipoDoc,
              doc: _docCtrl.text.trim(),
              apellidoPaterno: _apPaternoCtrl.text.trim(),
              apellidoMaterno: _apMaternoCtrl.text.trim().isEmpty ? null : _apMaternoCtrl.text.trim(),
              nombres: _nombresCtrl.text.trim(),
              profesion: _profesion,
              colegioProfesional: _colegioCtrl.text.trim().isEmpty ? null : _colegioCtrl.text.trim(),
              colegiatura: _colegiaturaCtrl.text.trim().isEmpty ? null : _colegiaturaCtrl.text.trim(),
              correo: _correoCtrl.text.trim().isEmpty ? null : _correoCtrl.text.trim(),
              celular: _celularCtrl.text.trim().isEmpty ? null : _celularCtrl.text.trim(),
              rne: _rneCtrl.text.trim().isEmpty ? null : _rneCtrl.text.trim(),
              esSerums: _esSerums,
              periodoSerums: _esSerums == 'SI' ? (_periodoSerums ?? _periodosDisponibles.first) : null,
            );
            Navigator.pop(context, t);
          },
          child: const Text('Guardar'),
        ),
      ],
    );
  }

  Widget _textField(String label, TextEditingController ctrl, {bool requerido = false}) {
    return TextFormField(
      controller: ctrl,
      validator: requerido ? (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null : null,
      decoration: InputDecoration(
        labelText: label,
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
      onChanged: onChanged,
    );
  }
}
