import 'package:flutter/material.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_computo.dart';
import '../models/monitoreo_modulo.dart';
import '../repositories/acta_repository.dart';
import 'modulos/rrhh_screen.dart';

/// Pantalla de detalle de una acta recién guardada en el dispositivo: punto
/// de entrada a los módulos que se capturan por separado (hoy: RR.HH.) y
/// resumen de lo ya registrado en el diálogo de creación (equipos, pozo a
/// tierra, panel solar).
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
  List<MonitoreoModulo> _modulos = [];
  List<EquipoComputo> _equipos = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    final acta = await _actaRepo.obtenerPorOfflineId(widget.offlineId);
    final modulos = await _actaRepo.obtenerModulos(widget.offlineId);
    final equipos = await _actaRepo.obtenerEquipos(widget.offlineId);
    if (!mounted) return;
    setState(() {
      _acta = acta;
      _modulos = modulos;
      _equipos = equipos;
      _isLoading = false;
    });
  }

  int get _trabajadoresRRHH {
    final rrhh = _modulos.where((m) => m.moduloNombre == ActaRepository.moduloRRHH);
    if (rrhh.isEmpty) return 0;
    // Conteo aproximado sin decodificar JSON completo aquí; el detalle real vive en RRHHScreen.
    return RegExp('"id"').allMatches(rrhh.first.contenido).length;
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
                const Text('Módulos de la ficha', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
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
                    title: const Text('01. Recursos Humanos', style: TextStyle(fontWeight: FontWeight.bold)),
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
                const SizedBox(height: 8),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 4),
                  child: Text(
                    'Los demás módulos de la ficha (Infraestructura, Enfermería, Medicina Familiar, etc.) todavía no están disponibles en la app de campo.',
                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                  ),
                ),
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
