import 'dart:convert';
import 'package:flutter/material.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_computo.dart';
import '../models/monitoreo_modulo.dart';
import '../repositories/acta_repository.dart';
import 'modulos/consultorio_dinamico_screen.dart';
import 'modulos/croquis_editor_screen.dart';
import 'modulos/rrhh_screen.dart';

/// Pantalla de detalle de una acta recién guardada en el dispositivo: punto
/// de entrada al módulo fijo (RR.HH.) y a los consultorios dinámicos que el
/// auditor va agregando libremente durante la visita — espejo de
/// resources/views/usuario/monitoreo/modulos.blade.php.
class ActaDetalleScreen extends StatefulWidget {
  final String offlineId;
  final String establecimientoNombre;

  const ActaDetalleScreen({super.key, required this.offlineId, required this.establecimientoNombre});

  @override
  State<ActaDetalleScreen> createState() => _ActaDetalleScreenState();
}

class _ActaDetalleScreenState extends State<ActaDetalleScreen> {
  final ActaRepository _actaRepo = ActaRepository();
  CabeceraMonitoreo? _acta;
  List<MonitoreoModulo> _consultorios = [];
  List<EquipoComputo> _equipos = [];
  int _trabajadoresRRHH = 0;
  int _elementosCroquis = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    final acta = await _actaRepo.obtenerPorOfflineId(widget.offlineId);
    final consultorios = await _actaRepo.obtenerConsultoriosDinamicos(widget.offlineId);
    final equipos = await _actaRepo.obtenerEquipos(widget.offlineId);
    final rrhh = await _actaRepo.obtenerModuloRRHH(widget.offlineId);
    final croquis = await _actaRepo.obtenerModuloInfraestructura2D(widget.offlineId);

    int trabajadores = 0;
    if (rrhh != null) {
      try {
        final data = jsonDecode(rrhh.contenido) as Map<String, dynamic>;
        trabajadores = (data['trabajadores'] as List? ?? []).length;
      } catch (_) {}
    }

    int elementosCroquis = 0;
    if (croquis != null) {
      try {
        final data = jsonDecode(croquis.contenido) as Map<String, dynamic>;
        elementosCroquis = (data['elementos'] as List? ?? []).length;
      } catch (_) {}
    }

    if (!mounted) return;
    setState(() {
      _acta = acta;
      _consultorios = consultorios;
      _equipos = equipos;
      _trabajadoresRRHH = trabajadores;
      _elementosCroquis = elementosCroquis;
      _isLoading = false;
    });
  }

  String _tituloDe(MonitoreoModulo m) {
    try {
      final data = jsonDecode(m.contenido) as Map<String, dynamic>;
      return (data['titulo_consultorio'] as String?)?.trim().isNotEmpty == true
          ? data['titulo_consultorio']
          : m.moduloNombre;
    } catch (_) {
      return m.moduloNombre;
    }
  }

  Future<void> _abrirNuevoConsultorio() async {
    final ctrl = TextEditingController();
    final titulo = await showDialog<String>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Nuevo Consultorio'),
        content: TextField(
          controller: ctrl,
          autofocus: true,
          textCapitalization: TextCapitalization.characters,
          decoration: InputDecoration(
            labelText: 'Nombre del consultorio',
            hintText: 'Ej: TRIAJE, FARMACIA, MEDICINA GENERAL...',
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cancelar')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(dialogCtx, ctrl.text.trim()),
            child: const Text('Crear'),
          ),
        ],
      ),
    );

    if (titulo == null || titulo.isEmpty) return;

    final slug = await _actaRepo.crearConsultorio(widget.offlineId, titulo);
    if (!mounted) return;

    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ConsultorioDinamicoScreen(
          actaOfflineId: widget.offlineId,
          moduloNombre: slug,
          establecimientoNombre: widget.establecimientoNombre,
        ),
      ),
    );
    _cargar();
  }

  Future<void> _eliminarConsultorio(MonitoreoModulo m) async {
    await _actaRepo.eliminarConsultorio(widget.offlineId, m.moduloNombre);
    _cargar();
  }

  @override
  Widget build(BuildContext context) {
    final acta = _acta;
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Text('Detalle de Acta'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      floatingActionButton: acta == null
          ? null
          : FloatingActionButton.extended(
              onPressed: _abrirNuevoConsultorio,
              backgroundColor: const Color(0xFF4F46E5),
              icon: const Icon(Icons.add_business_rounded),
              label: const Text('Nuevo Consultorio'),
            ),
      body: _isLoading || acta == null
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: const BorderSide(color: Color(0xFFE2E8F0))),
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(widget.establecimientoNombre, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                        const SizedBox(height: 6),
                        Text('Fecha: ${acta.fecha} · Responsable: ${acta.responsable}', style: const TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(Icons.circle, size: 8, color: acta.syncStatus == 'synced' ? const Color(0xFF15803D) : const Color(0xFFB45309)),
                            const SizedBox(width: 6),
                            Text(
                              acta.syncStatus == 'synced' ? 'Sincronizada con el servidor' : 'Pendiente de sincronizar',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: acta.syncStatus == 'synced' ? const Color(0xFF15803D) : const Color(0xFFB45309)),
                            ),
                          ],
                        ),
                        const Divider(height: 24),
                        _infoRow('Pozo a tierra', acta.pozoTierra == 'SI' ? '${acta.pozoTierra} (${acta.pozoTierraOperativos ?? 0} operativos de ${acta.pozoTierraCantidad ?? 0})' : 'NO'),
                        _infoRow('Panel solar', acta.panelSolar == 'SI' ? '${acta.panelSolar} (${acta.panelSolarOperativos ?? 0} operativos de ${acta.panelSolarCantidad ?? 0})' : 'NO'),
                        _infoRow('Equipos de cómputo inventariados', '${_equipos.length}'),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                const Text('Módulo fijo', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                const SizedBox(height: 10),
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: Color(0xFFE2E8F0))),
                  child: ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.badge_outlined, color: Color(0xFF4F46E5)),
                    ),
                    title: const Text('Recursos Humanos', style: TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text(_trabajadoresRRHH > 0 ? '$_trabajadoresRRHH trabajador(es) registrado(s)' : 'Sin registrar'),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => RRHHScreen(actaOfflineId: widget.offlineId, establecimientoNombre: widget.establecimientoNombre),
                        ),
                      );
                      _cargar();
                    },
                  ),
                ),
                const SizedBox(height: 10),
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: Color(0xFFE2E8F0))),
                  child: ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.architecture_rounded, color: Color(0xFF4F46E5)),
                    ),
                    title: const Text('Infraestructura 2D (Croquis)', style: TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text(_elementosCroquis > 0 ? '$_elementosCroquis elemento(s) en el plano' : 'Sin registrar'),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => CroquisEditorScreen(actaOfflineId: widget.offlineId, establecimientoNombre: widget.establecimientoNombre),
                        ),
                      );
                      _cargar();
                    },
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Text('Consultorios (${_consultorios.length})', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                  ],
                ),
                const SizedBox(height: 10),
                if (_consultorios.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(28),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: const Color(0xFFE2E8F0))),
                    child: const Column(
                      children: [
                        Icon(Icons.meeting_room_outlined, size: 40, color: Color(0xFFCBD5E1)),
                        SizedBox(height: 10),
                        Text('Aún no se agregó ningún consultorio. Usa "Nuevo Consultorio" para empezar (ej. Triaje, Farmacia, Medicina General).', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w500), textAlign: TextAlign.center),
                      ],
                    ),
                  )
                else
                  ..._consultorios.map((c) => Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: Color(0xFFE2E8F0))),
                        child: ListTile(
                          leading: Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(10)),
                            child: const Icon(Icons.meeting_room_rounded, color: Color(0xFF64748B)),
                          ),
                          title: Text(_tituloDe(c), style: const TextStyle(fontWeight: FontWeight.bold)),
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.delete_outline_rounded, color: Color(0xFFEF4444)),
                                onPressed: () => _eliminarConsultorio(c),
                              ),
                              const Icon(Icons.chevron_right_rounded),
                            ],
                          ),
                          onTap: () async {
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ConsultorioDinamicoScreen(
                                  actaOfflineId: widget.offlineId,
                                  moduloNombre: c.moduloNombre,
                                  establecimientoNombre: widget.establecimientoNombre,
                                ),
                              ),
                            );
                            _cargar();
                          },
                        ),
                      )),
                const SizedBox(height: 90),
              ],
            ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          Expanded(child: Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 13))),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF1E293B))),
        ],
      ),
    );
  }
}
