import 'dart:convert';
import 'package:flutter/material.dart';
import '../models/establecimiento.dart';
import '../repositories/establecimiento_repository.dart';

/// Formulario de edición de un establecimiento — mismas 6 secciones que
/// resources/views/usuario/establecimientos/edit.blade.php: Información
/// General, Director Médico/Responsable Legal, Ubicación y Estructura,
/// Detalles Operativos e Infraestructura, Estructura Redes MINSA, y
/// UPSS/UPS (solo lectura, se llenan al Consultar RENIPRESS). El código
/// SUSALUD es de solo lectura, igual que en la web.
class EstablecimientoEditScreen extends StatefulWidget {
  final Establecimiento establecimiento;

  const EstablecimientoEditScreen({super.key, required this.establecimiento});

  @override
  State<EstablecimientoEditScreen> createState() => _EstablecimientoEditScreenState();
}

class _EstablecimientoEditScreenState extends State<EstablecimientoEditScreen> {
  final _formKey = GlobalKey<FormState>();
  final _repo = EstablecimientoRepository();

  late final Map<String, TextEditingController> _c;
  late String _tipoDocumento;
  List<Map<String, dynamic>> _upss = [];
  List<Map<String, dynamic>> _ups = [];

  bool _guardando = false;
  bool _consultando = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    final e = widget.establecimiento;
    _c = {
      'codigo': TextEditingController(text: e.codigo),
      'nombre': TextEditingController(text: e.nombre),
      'institucion': TextEditingController(text: e.institucion ?? ''),
      'categoria': TextEditingController(text: e.categoria),
      'estado': TextEditingController(text: e.estado ?? ''),
      'condicion': TextEditingController(text: e.condicion ?? ''),
      'responsable': TextEditingController(text: e.responsable),
      'numero_documento': TextEditingController(text: e.numeroDocumento ?? ''),
      'colegio_profesional': TextEditingController(text: e.colegioProfesional ?? ''),
      'colegiatura': TextEditingController(text: e.colegiatura ?? ''),
      'rne': TextEditingController(text: e.rne ?? ''),
      'departamento': TextEditingController(text: e.departamento ?? ''),
      'provincia': TextEditingController(text: e.provincia),
      'distrito': TextEditingController(text: e.distrito),
      'centro_poblado': TextEditingController(text: e.centroPoblado ?? ''),
      'direccion': TextEditingController(text: e.direccion ?? ''),
      'latitud': TextEditingController(text: e.latitud?.toString() ?? ''),
      'longitud': TextEditingController(text: e.longitud?.toString() ?? ''),
      'altitud': TextEditingController(text: e.altitud ?? ''),
      'telefono': TextEditingController(text: e.telefono ?? ''),
      'correo': TextEditingController(text: e.correo ?? ''),
      'horario_atencion': TextEditingController(text: e.horarioAtencion ?? ''),
      'numero_ambientes': TextEditingController(text: e.numeroAmbientes ?? ''),
      'numero_camas': TextEditingController(text: e.numeroCamas ?? ''),
      'numero_resolucion_creacion': TextEditingController(text: e.numeroResolucionCreacion ?? ''),
      'fecha_creacion_resolucion': TextEditingController(text: e.fechaCreacionResolucion ?? ''),
      'fecha_registro': TextEditingController(text: e.fechaRegistro ?? ''),
      'red': TextEditingController(text: e.red),
      'microred': TextEditingController(text: e.microred),
      'clas': TextEditingController(text: e.clas ?? ''),
      'odsis': TextEditingController(text: e.odsis ?? ''),
    };
    _tipoDocumento = (e.tipoDocumento == 'CEX' ? 'CE' : e.tipoDocumento) ?? '';
    _upss = _parseLista(e.upss);
    _ups = _parseLista(e.ups);
  }

  List<Map<String, dynamic>> _parseLista(String? raw) {
    if (raw == null || raw.isEmpty) return [];
    try {
      return List<Map<String, dynamic>>.from(json.decode(raw));
    } catch (_) {
      return [];
    }
  }

  @override
  void dispose() {
    for (final ctrl in _c.values) {
      ctrl.dispose();
    }
    super.dispose();
  }

  Future<void> _consultarRenipress() async {
    setState(() => _consultando = true);
    final res = await _repo.consultarRenipress(widget.establecimiento.id!);
    if (!mounted) return;
    setState(() => _consultando = false);

    if (res['success'] != true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: const Color(0xFFB91C1C),
        content: Text(res['message'] ?? 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.'),
      ));
      return;
    }

    final d = Map<String, dynamic>.from(res['datos']);
    setState(() {
      _c['nombre']!.text = d['nombre'] ?? _c['nombre']!.text;
      _c['institucion']!.text = d['institucion'] ?? _c['institucion']!.text;
      _c['categoria']!.text = d['categoria'] ?? _c['categoria']!.text;
      _c['estado']!.text = d['estado'] ?? _c['estado']!.text;
      _c['condicion']!.text = d['condicion'] ?? _c['condicion']!.text;

      final medico = d['director_medico'] as Map<String, dynamic>?;
      if (medico != null) {
        _c['responsable']!.text = medico['nombres'] ?? _c['responsable']!.text;
        _tipoDocumento = (medico['tipo_documento'] ?? _tipoDocumento).toString();
        _c['numero_documento']!.text = medico['numero_documento'] ?? _c['numero_documento']!.text;
        _c['colegio_profesional']!.text = medico['colegio_profesional'] ?? _c['colegio_profesional']!.text;
        _c['colegiatura']!.text = medico['colegiatura'] ?? _c['colegiatura']!.text;
        _c['rne']!.text = medico['rne'] ?? _c['rne']!.text;
      }

      _c['departamento']!.text = d['departamento'] ?? _c['departamento']!.text;
      _c['provincia']!.text = d['provincia'] ?? _c['provincia']!.text;
      _c['distrito']!.text = d['distrito'] ?? _c['distrito']!.text;
      _c['centro_poblado']!.text = d['centro_poblado'] ?? _c['centro_poblado']!.text;
      _c['direccion']!.text = d['direccion'] ?? _c['direccion']!.text;
      _c['latitud']!.text = (d['latitud'] ?? _c['latitud']!.text).toString();
      _c['longitud']!.text = (d['longitud'] ?? _c['longitud']!.text).toString();
      _c['altitud']!.text = (d['altitud'] ?? _c['altitud']!.text).toString();

      _c['telefono']!.text = d['telefono'] ?? _c['telefono']!.text;
      _c['correo']!.text = d['correo'] ?? _c['correo']!.text;
      _c['horario_atencion']!.text = d['horario_atencion'] ?? _c['horario_atencion']!.text;
      _c['numero_ambientes']!.text = (d['numero_ambientes'] ?? _c['numero_ambientes']!.text).toString();
      _c['numero_camas']!.text = (d['numero_camas'] ?? _c['numero_camas']!.text).toString();
      _c['numero_resolucion_creacion']!.text = d['numero_resolucion_creacion'] ?? _c['numero_resolucion_creacion']!.text;
      _c['fecha_creacion_resolucion']!.text = d['fecha_creacion_resolucion'] ?? _c['fecha_creacion_resolucion']!.text;
      _c['fecha_registro']!.text = d['fecha_registro'] ?? _c['fecha_registro']!.text;

      final minsa = d['minsa'] as Map<String, dynamic>?;
      if (minsa != null) {
        _c['red']!.text = minsa['red'] ?? _c['red']!.text;
        _c['microred']!.text = minsa['microred'] ?? _c['microred']!.text;
        _c['clas']!.text = minsa['clas'] ?? _c['clas']!.text;
        _c['odsis']!.text = minsa['odsis'] ?? _c['odsis']!.text;
      }

      _upss = List<Map<String, dynamic>>.from(d['upss'] ?? []);
      _ups = List<Map<String, dynamic>>.from(d['ups'] ?? []);
    });

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
      backgroundColor: Color(0xFF15803D),
      content: Text('Se obtuvieron los datos actualizados de RENIPRESS. Revise y guarde.'),
    ));
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _guardando = true;
      _error = null;
    });

    final payload = <String, dynamic>{
      'nombre': _c['nombre']!.text.trim(),
      'codigo': _c['codigo']!.text.trim(),
      'institucion': _c['institucion']!.text.trim(),
      'direccion': _c['direccion']!.text.trim(),
      'departamento': _c['departamento']!.text.trim(),
      'provincia': _c['provincia']!.text.trim(),
      'distrito': _c['distrito']!.text.trim(),
      'centro_poblado': _c['centro_poblado']!.text.trim(),
      'telefono': _c['telefono']!.text.trim(),
      'correo': _c['correo']!.text.trim(),
      'red': _c['red']!.text.trim(),
      'microred': _c['microred']!.text.trim(),
      'clas': _c['clas']!.text.trim(),
      'odsis': _c['odsis']!.text.trim(),
      'responsable': _c['responsable']!.text.trim(),
      'tipo_documento': _tipoDocumento,
      'numero_documento': _c['numero_documento']!.text.trim(),
      'colegio_profesional': _c['colegio_profesional']!.text.trim(),
      'colegiatura': _c['colegiatura']!.text.trim(),
      'rne': _c['rne']!.text.trim(),
      'categoria': _c['categoria']!.text.trim(),
      'estado': _c['estado']!.text.trim(),
      'condicion': _c['condicion']!.text.trim(),
      'latitud': double.tryParse(_c['latitud']!.text.trim()),
      'longitud': double.tryParse(_c['longitud']!.text.trim()),
      'altitud': _c['altitud']!.text.trim(),
      'fecha_creacion_resolucion': _c['fecha_creacion_resolucion']!.text.trim(),
      'fecha_registro': _c['fecha_registro']!.text.trim(),
      'numero_resolucion_creacion': _c['numero_resolucion_creacion']!.text.trim(),
      'horario_atencion': _c['horario_atencion']!.text.trim(),
      'numero_ambientes': _c['numero_ambientes']!.text.trim(),
      'numero_camas': _c['numero_camas']!.text.trim(),
      if (_upss.isNotEmpty) 'upss': _upss,
      if (_ups.isNotEmpty) 'ups': _ups,
    };

    final res = await _repo.actualizar(widget.establecimiento.id!, payload);
    if (!mounted) return;
    setState(() => _guardando = false);

    if (res['success'] == true) {
      Navigator.pop(context, true);
    } else {
      final errors = res['errors'] as Map<String, dynamic>?;
      setState(() => _error = errors != null
          ? errors.values.map((v) => (v as List).first).join('\n')
          : (res['message'] ?? 'No se pudo guardar el establecimiento.'));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(widget.establecimiento.nombre),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Center(
              child: OutlinedButton.icon(
                onPressed: _consultando ? null : _consultarRenipress,
                style: OutlinedButton.styleFrom(foregroundColor: Colors.white, side: const BorderSide(color: Colors.white54)),
                icon: _consultando
                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.refresh_rounded, size: 16),
                label: const Text('Consultar RENIPRESS'),
              ),
            ),
          ),
        ],
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
                if (_error != null)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: const Color(0xFFFEE2E2), borderRadius: BorderRadius.circular(10)),
                    child: Text(_error!, style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 12)),
                  ),

                _seccion('Información General', const Color(0xFF3B82F6), [
                  _campo('codigo', 'Código Único (RENIPRESS)', readOnly: true),
                  _campo('nombre', 'Nombre del Establecimiento', requerido: true),
                  _campo('institucion', 'Institución / Entidad'),
                  _campo('categoria', 'Categoría'),
                  _campo('estado', 'Estado RENIPRESS'),
                  _campo('condicion', 'Condición RENIPRESS'),
                ]),

                _seccion('Director Médico / Responsable Legal', const Color(0xFF6366F1), [
                  _campo('responsable', 'Nombre Completo del Jefe / Responsable'),
                  DropdownButtonFormField<String>(
                    initialValue: _tipoDocumento.isEmpty ? null : _tipoDocumento,
                    decoration: const InputDecoration(labelText: 'Tipo de Documento', isDense: true, filled: true, fillColor: Colors.white),
                    items: const [
                      DropdownMenuItem(value: 'DNI', child: Text('DNI')),
                      DropdownMenuItem(value: 'CE', child: Text('CE (Carné de Extranjería)')),
                    ],
                    onChanged: (v) => setState(() => _tipoDocumento = v ?? ''),
                  ),
                  _campo('numero_documento', 'Número de Documento'),
                  _campo('colegio_profesional', 'Colegio Profesional'),
                  _campo('colegiatura', 'N° Colegiatura'),
                  _campo('rne', 'RNE (Registro Esp.)'),
                ]),

                _seccion('Ubicación y Estructura', const Color(0xFF10B981), [
                  _campo('departamento', 'Departamento'),
                  _campo('provincia', 'Provincia', requerido: true),
                  _campo('distrito', 'Distrito', requerido: true),
                  _campo('centro_poblado', 'Centro Poblado'),
                  _campo('direccion', 'Dirección del Establecimiento'),
                  _campo('latitud', 'Latitud'),
                  _campo('longitud', 'Longitud'),
                  _campo('altitud', 'Altitud (m.s.n.m.)'),
                ]),

                _seccion('Detalles Operativos e Infraestructura', const Color(0xFFF59E0B), [
                  _campo('telefono', 'Teléfono de Contacto'),
                  _campo('correo', 'Correo Electrónico'),
                  _campo('horario_atencion', 'Horario de Atención'),
                  _campo('numero_ambientes', 'N° de Ambientes'),
                  _campo('numero_camas', 'N° de Camas'),
                  _campo('numero_resolucion_creacion', 'N° Resolución Creación'),
                  _campo('fecha_creacion_resolucion', 'Fecha Resolución Creación'),
                  _campo('fecha_registro', 'Fecha de Registro'),
                ]),

                _seccion('Estructura Redes MINSA', const Color(0xFF06B6D4), [
                  _campo('red', 'Red de Salud'),
                  _campo('microred', 'Microred de Salud'),
                  _campo('clas', 'CLAS'),
                  _campo('odsis', 'ODSIS'),
                ]),

                _seccionUpssUps(),

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
                    label: const Text('Guardar Cambios', style: TextStyle(fontWeight: FontWeight.bold)),
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

  Widget _campo(String key, String label, {bool requerido = false, bool readOnly = false}) {
    return SizedBox(
      width: 260,
      child: TextFormField(
        controller: _c[key],
        readOnly: readOnly,
        decoration: InputDecoration(
          labelText: label,
          isDense: true,
          filled: true,
          fillColor: readOnly ? const Color(0xFFF1F5F9) : Colors.white,
        ),
        validator: requerido ? (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null : null,
      ),
    );
  }

  Widget _seccion(String titulo, Color color, List<Widget> campos) {
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
          Row(
            children: [
              Container(width: 4, height: 20, decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4))),
              const SizedBox(width: 10),
              Text(titulo.toUpperCase(), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B), letterSpacing: 0.5)),
            ],
          ),
          const SizedBox(height: 16),
          Wrap(spacing: 14, runSpacing: 14, children: campos),
        ],
      ),
    );
  }

  Widget _seccionUpssUps() {
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
          Row(
            children: [
              Container(width: 4, height: 20, decoration: BoxDecoration(color: const Color(0xFFA855F7), borderRadius: BorderRadius.circular(4))),
              const SizedBox(width: 10),
              const Expanded(
                child: Text('UNIDADES PRESTADORAS (UPSS) Y SERVICIOS (UPS)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B), letterSpacing: 0.5)),
              ),
            ],
          ),
          const SizedBox(height: 16),
          LayoutBuilder(builder: (context, constraints) {
            final ancho = constraints.maxWidth > 700 ? (constraints.maxWidth - 16) / 2 : constraints.maxWidth;
            return Wrap(
              spacing: 16,
              runSpacing: 16,
              children: [
                SizedBox(width: ancho, child: _listaUpssUps('UPSS (Unidades Prestadoras)', _upss, const Color(0xFFA855F7))),
                SizedBox(width: ancho, child: _listaUpssUps('UPS (Servicios de Salud)', _ups, const Color(0xFF3B82F6))),
              ],
            );
          }),
        ],
      ),
    );
  }

  Widget _listaUpssUps(String titulo, List<Map<String, dynamic>> items, Color color) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(14), border: Border.all(color: const Color(0xFFE2E8F0))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(child: Text(titulo, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF334155)))),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
                child: Text('${items.length} Registrados', style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (items.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Text('No hay registros. Consulte RENIPRESS para sincronizarlos.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11), textAlign: TextAlign.center),
            )
          else
            ConstrainedBox(
              constraints: const BoxConstraints(maxHeight: 220),
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: items.length,
                itemBuilder: (context, i) {
                  final item = items[i];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10), border: Border.all(color: const Color(0xFFE2E8F0))),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                          child: Text('${item['codigo'] ?? '-'}', style: TextStyle(color: color, fontSize: 9, fontWeight: FontWeight.bold)),
                        ),
                        const SizedBox(width: 8),
                        Expanded(child: Text('${item['nombre'] ?? ''}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)))),
                      ],
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
