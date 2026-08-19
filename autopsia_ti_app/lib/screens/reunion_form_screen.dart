import 'dart:convert';
import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/reunion.dart';
import '../repositories/reunion_repository.dart';

/// Formulario "Nueva Acta de Reunión" — mismos campos que
/// resources/views/usuario/actas_reunion/create.blade.php (ver informe de
/// auditoría): título, institución, fecha/hora, descripción, participantes /
/// acuerdos / comentarios como tablas dinámicas, y hasta 2 fotos. El QR de
/// asistencia y la firma digital viven 100% en el servidor Laravel (dependen
/// de conexión en vivo) y no se replican aquí — la app solo prepara el acta
/// para sincronizar.
class ReunionFormScreen extends StatefulWidget {
  const ReunionFormScreen({super.key});

  @override
  State<ReunionFormScreen> createState() => _ReunionFormScreenState();
}

class _ParticipanteRow {
  final tipoDocumento = TextEditingController(text: 'DNI');
  final dni = TextEditingController();
  final apellidos = TextEditingController();
  final nombres = TextEditingController();
  final cargo = TextEditingController();
  final institucion = TextEditingController();
  final celular = TextEditingController();

  void dispose() {
    dni.dispose();
    apellidos.dispose();
    nombres.dispose();
    cargo.dispose();
    institucion.dispose();
    celular.dispose();
  }
}

class _ReunionFormScreenState extends State<ReunionFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _tituloCtrl = TextEditingController();
  final _institucionCtrl = TextEditingController();
  final _descripcionCtrl = TextEditingController();
  DateTime? _fecha;
  TimeOfDay? _horaInicio;
  TimeOfDay? _horaFin;

  final List<_ParticipanteRow> _participantes = [];
  final List<TextEditingController> _acuerdos = [];
  final List<TextEditingController> _comentarios = [];

  String? _foto1Path;
  String? _foto2Path;
  bool _guardando = false;

  @override
  void initState() {
    super.initState();
    _prellenarCreador();
    _acuerdos.add(TextEditingController());
    _comentarios.add(TextEditingController());
  }

  Future<void> _prellenarCreador() async {
    final prefs = await SharedPreferences.getInstance();
    final nombreCompleto = prefs.getString('user_name') ?? '';
    final dni = prefs.getString('user_email') ?? '';
    final row = _ParticipanteRow();
    if (nombreCompleto.contains(',')) {
      final partes = nombreCompleto.split(',');
      row.apellidos.text = partes[0].trim();
      row.nombres.text = partes.length > 1 ? partes[1].trim() : '';
    } else {
      row.nombres.text = nombreCompleto;
    }
    row.dni.text = dni;
    row.cargo.text = 'IMPLEMENTADOR';
    row.institucion.text = 'CONSORCIO TRANSFORMACION DIGITAL';
    if (mounted) setState(() => _participantes.add(row));
  }

  @override
  void dispose() {
    _tituloCtrl.dispose();
    _institucionCtrl.dispose();
    _descripcionCtrl.dispose();
    for (final p in _participantes) {
      p.dispose();
    }
    for (final a in _acuerdos) {
      a.dispose();
    }
    for (final c in _comentarios) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _elegirFecha() async {
    final res = await showDatePicker(
      context: context,
      initialDate: _fecha ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
    );
    if (res != null) setState(() => _fecha = res);
  }

  Future<void> _elegirHora({required bool inicio}) async {
    final res = await showTimePicker(context: context, initialTime: TimeOfDay.now());
    if (res != null) {
      setState(() {
        if (inicio) {
          _horaInicio = res;
        } else {
          _horaFin = res;
        }
      });
    }
  }

  Future<void> _elegirFoto({required bool primera}) async {
    final res = await FilePicker.platform.pickFiles(type: FileType.image);
    if (res == null || res.files.isEmpty || res.files.first.path == null) return;
    setState(() {
      if (primera) {
        _foto1Path = res.files.first.path;
      } else {
        _foto2Path = res.files.first.path;
      }
    });
  }

  String _fmtHora(TimeOfDay? t) {
    if (t == null) return '';
    final h = t.hour.toString().padLeft(2, '0');
    final m = t.minute.toString().padLeft(2, '0');
    return '$h:$m';
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    if (_fecha == null || _horaInicio == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFFB91C1C), content: Text('Complete fecha y hora de inicio.')),
      );
      return;
    }
    final participantesValidos = _participantes.where((p) => p.apellidos.text.trim().isNotEmpty && p.nombres.text.trim().isNotEmpty).toList();
    if (participantesValidos.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFFB91C1C), content: Text('Agregue al menos un participante con apellidos y nombres.')),
      );
      return;
    }

    setState(() => _guardando = true);

    final acuerdosJson = jsonEncode(
      _acuerdos.where((a) => a.text.trim().isNotEmpty).map((a) => {'descripcion': a.text.trim()}).toList(),
    );
    final comentariosJson = jsonEncode(
      _comentarios.where((c) => c.text.trim().isNotEmpty).map((c) => {'descripcion': c.text.trim()}).toList(),
    );
    final participantesJson = jsonEncode(
      participantesValidos
          .map((p) => {
                'tipo_documento': p.tipoDocumento.text.trim().isEmpty ? 'DNI' : p.tipoDocumento.text.trim(),
                'dni': p.dni.text.trim(),
                'apellidos': p.apellidos.text.trim(),
                'nombres': p.nombres.text.trim(),
                'cargo': p.cargo.text.trim(),
                'institucion': p.institucion.text.trim(),
                'celular': p.celular.text.trim(),
              })
          .toList(),
    );

    final offlineId = 'REUNION-${DateTime.now().millisecondsSinceEpoch}';
    final reunion = Reunion(
      offlineId: offlineId,
      localCreatedAt: DateTime.now().toIso8601String(),
      tituloReunion: _tituloCtrl.text.trim().toUpperCase(),
      fechaReunion: '${_fecha!.year.toString().padLeft(4, '0')}-${_fecha!.month.toString().padLeft(2, '0')}-${_fecha!.day.toString().padLeft(2, '0')}',
      horaReunion: _fmtHora(_horaInicio),
      horaFinalizadaReunion: _horaFin != null ? _fmtHora(_horaFin) : null,
      nombreInstitucion: _institucionCtrl.text.trim().toUpperCase(),
      descripcionGeneral: _descripcionCtrl.text.trim(),
      acuerdos: acuerdosJson,
      comentariosObservaciones: comentariosJson,
      participantes: participantesJson,
      foto1: _foto1Path,
      foto2: _foto2Path,
    );

    await ReunionRepository().guardar(reunion);
    if (!mounted) return;
    setState(() => _guardando = false);
    Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Nueva Acta de Reunión'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 900),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _seccion('Datos Generales', [
                  TextFormField(
                    controller: _tituloCtrl,
                    textCapitalization: TextCapitalization.characters,
                    decoration: const InputDecoration(labelText: 'Título de Reunión', filled: true, fillColor: Colors.white),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _institucionCtrl,
                    textCapitalization: TextCapitalization.characters,
                    decoration: const InputDecoration(labelText: 'Nombre Institución', filled: true, fillColor: Colors.white),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _elegirFecha,
                          icon: const Icon(Icons.calendar_today_rounded, size: 16),
                          label: Text(_fecha == null ? 'Fecha' : '${_fecha!.year}-${_fecha!.month.toString().padLeft(2, '0')}-${_fecha!.day.toString().padLeft(2, '0')}'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _elegirHora(inicio: true),
                          icon: const Icon(Icons.access_time_rounded, size: 16),
                          label: Text(_horaInicio == null ? 'Hora Inicio' : _fmtHora(_horaInicio)),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _elegirHora(inicio: false),
                          icon: const Icon(Icons.access_time_filled_rounded, size: 16),
                          label: Text(_horaFin == null ? 'Hora Fin (opcional)' : _fmtHora(_horaFin)),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _descripcionCtrl,
                    maxLines: 4,
                    decoration: const InputDecoration(labelText: 'Descripción General', filled: true, fillColor: Colors.white, alignLabelWithHint: true),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                  ),
                ]),

                _seccion('Participantes', [
                  ..._participantes.asMap().entries.map((entry) => _filaParticipante(entry.key, entry.value)),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => setState(() => _participantes.add(_ParticipanteRow())),
                    icon: const Icon(Icons.person_add_alt_1_rounded, size: 16),
                    label: const Text('Agregar participante'),
                  ),
                ]),

                _seccion('Acuerdos', [
                  ..._acuerdos.asMap().entries.map((e) => _filaTexto(e.value, () => setState(() {
                        _acuerdos.removeAt(e.key);
                      }))),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => setState(() => _acuerdos.add(TextEditingController())),
                    icon: const Icon(Icons.add_rounded, size: 16),
                    label: const Text('Agregar acuerdo'),
                  ),
                ]),

                _seccion('Comentarios / Observaciones', [
                  ..._comentarios.asMap().entries.map((e) => _filaTexto(e.value, () => setState(() {
                        _comentarios.removeAt(e.key);
                      }))),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => setState(() => _comentarios.add(TextEditingController())),
                    icon: const Icon(Icons.add_rounded, size: 16),
                    label: const Text('Agregar comentario'),
                  ),
                ]),

                _seccion('Evidencia Fotográfica (máx. 2)', [
                  Row(
                    children: [
                      Expanded(child: _fotoSlot(_foto1Path, () => _elegirFoto(primera: true), () => setState(() => _foto1Path = null))),
                      const SizedBox(width: 12),
                      Expanded(child: _fotoSlot(_foto2Path, () => _elegirFoto(primera: false), () => setState(() => _foto2Path = null))),
                    ],
                  ),
                ]),

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

  Widget _seccion(String titulo, List<Widget> children) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(titulo, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
          const SizedBox(height: 14),
          ...children,
        ],
      ),
    );
  }

  Widget _filaParticipante(int index, _ParticipanteRow row) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(10)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text('N° ${index + 1}${index == 0 ? ' (Creador)' : ''}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF4F46E5))),
              const Spacer(),
              if (_participantes.length > 1)
                IconButton(
                  icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFB91C1C)),
                  onPressed: () => setState(() {
                    row.dispose();
                    _participantes.removeAt(index);
                  }),
                ),
            ],
          ),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              _campoAncho(row.dni, 'Documento'),
              _campoAncho(row.apellidos, 'Apellidos', requerido: true),
              _campoAncho(row.nombres, 'Nombres', requerido: true),
              _campoAncho(row.cargo, 'Cargo'),
              _campoAncho(row.institucion, 'Institución'),
              _campoAncho(row.celular, 'Celular'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _campoAncho(TextEditingController ctrl, String label, {bool requerido = false}) {
    return SizedBox(
      width: 200,
      child: TextFormField(
        controller: ctrl,
        decoration: InputDecoration(labelText: label, isDense: true, filled: true, fillColor: Colors.white),
        validator: requerido ? (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null : null,
      ),
    );
  }

  Widget _filaTexto(TextEditingController ctrl, VoidCallback onRemove) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Expanded(
            child: TextFormField(
              controller: ctrl,
              decoration: const InputDecoration(isDense: true, filled: true, fillColor: Color(0xFFF8FAFC), hintText: 'Descripción...'),
            ),
          ),
          IconButton(icon: const Icon(Icons.close_rounded, size: 18, color: Color(0xFF94A3B8)), onPressed: onRemove),
        ],
      ),
    );
  }

  Widget _fotoSlot(String? path, VoidCallback onPick, VoidCallback onRemove) {
    return Container(
      height: 120,
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFCBD5E1), style: BorderStyle.solid),
      ),
      child: path == null
          ? InkWell(
              onTap: onPick,
              borderRadius: BorderRadius.circular(12),
              child: const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.add_a_photo_outlined, color: Color(0xFF94A3B8)),
                    SizedBox(height: 6),
                    Text('Agregar foto', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                  ],
                ),
              ),
            )
          : Stack(
              fit: StackFit.expand,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(File(path), fit: BoxFit.cover),
                ),
                Positioned(
                  top: 4,
                  right: 4,
                  child: InkWell(
                    onTap: onRemove,
                    child: const CircleAvatar(radius: 12, backgroundColor: Colors.black54, child: Icon(Icons.close_rounded, size: 14, color: Colors.white)),
                  ),
                ),
              ],
            ),
    );
  }
}
