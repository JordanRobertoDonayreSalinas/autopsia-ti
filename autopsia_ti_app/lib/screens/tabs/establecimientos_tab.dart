import 'package:flutter/material.dart';
import '../../ui/views/renipress_modal.dart';

/// Pestaña "Establecimientos": acceso a la consulta RENIPRESS/SUSALUD.
class EstablecimientosTab extends StatelessWidget {
  const EstablecimientosTab({super.key});

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
            const Text('Establecimientos (IPRESS)', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
            const SizedBox(height: 8),
            const Text('Consulte establecimientos de salud registrados en SUSALUD (RENIPRESS).', style: TextStyle(fontSize: 13, color: Color(0xFF64748B))),
            const SizedBox(height: 20),
            OutlinedButton.icon(
              onPressed: () => showDialog(context: context, builder: (_) => const RenipressModal()),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFF4F46E5)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.verified_rounded, color: Color(0xFF4F46E5), size: 18),
              label: const Text('Consultar RENIPRESS', style: TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}
