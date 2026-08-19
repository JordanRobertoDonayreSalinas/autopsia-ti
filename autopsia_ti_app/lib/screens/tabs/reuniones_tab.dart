import 'dart:convert';
import 'package:flutter/material.dart';
import '../../models/reunion.dart';

/// Pestaña "Actas de Reunión": lista las reuniones guardadas localmente.
/// Sin registros muestra un estado vacío en vez de un dato de ejemplo fijo.
class ReunionesTab extends StatelessWidget {
  final List<Reunion> reuniones;
  final VoidCallback onNuevaReunion;

  const ReunionesTab({super.key, required this.reuniones, required this.onNuevaReunion});

  int _contarJson(String? raw) {
    if (raw == null || raw.isEmpty) return 0;
    try {
      return (json.decode(raw) as List).length;
    } catch (_) {
      return 0;
    }
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
            Row(
              children: [
                const Text('Actas de Reunión de Campo', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                const Spacer(),
                ElevatedButton.icon(
                  onPressed: onNuevaReunion,
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('Nueva Reunión'),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (reuniones.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(32),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: const Column(
                  children: [
                    Icon(Icons.groups_outlined, size: 40, color: Color(0xFFCBD5E1)),
                    SizedBox(height: 10),
                    Text('Aún no hay actas de reunión registradas en este dispositivo.', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                  ],
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: reuniones.length,
                itemBuilder: (context, index) {
                  final r = reuniones[index];
                  final sincronizada = r.syncStatus == 'synced';
                  final numParticipantes = _contarJson(r.participantes);
                  final numAcuerdos = _contarJson(r.acuerdos);
                  return Card(
                    elevation: 0,
                    margin: const EdgeInsets.only(bottom: 10),
                    color: const Color(0xFFF8FAFC),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: Color(0xFFE2E8F0))),
                    child: ListTile(
                      leading: const Icon(Icons.group_rounded, color: Color(0xFF4F46E5)),
                      title: Text(r.tituloReunion, style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Text('${r.fechaReunion} ${r.horaReunion} · ${r.nombreInstitucion}\n$numParticipantes participante(s) · $numAcuerdos acuerdo(s)'),
                      isThreeLine: true,
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: sincronizada ? const Color(0xFFD1FAE5) : const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          sincronizada ? 'SINCRONIZADA' : 'PENDIENTE',
                          style: TextStyle(
                            color: sincronizada ? const Color(0xFF065F46) : const Color(0xFF92400E),
                            fontWeight: FontWeight.bold,
                            fontSize: 10,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
}
