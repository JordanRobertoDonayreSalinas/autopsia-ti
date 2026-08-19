import 'package:flutter/material.dart';

/// Pestaña "Reportes". Fuera de alcance del modo offline de campo (ver
/// informe de revisión, sección 6): se mantiene como acceso directo a los
/// reportes que hoy solo funcionan en la web.
class ReportesTab extends StatelessWidget {
  const ReportesTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: const Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Reportes', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
            SizedBox(height: 8),
            Text('Generación de reportes estadísticos y de campo.', style: TextStyle(fontSize: 13, color: Color(0xFF64748B))),
            SizedBox(height: 24),
            ListTile(
              leading: Icon(Icons.picture_as_pdf_rounded, color: Color(0xFFEF4444), size: 28),
              title: Text('Reporte General de Actas', style: TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text('Todas las actas de diagnóstico situacional'),
              trailing: Icon(Icons.download_rounded, color: Color(0xFF4F46E5)),
            ),
            ListTile(
              leading: Icon(Icons.bar_chart_rounded, color: Color(0xFF3B82F6), size: 28),
              title: Text('Reporte por Establecimiento', style: TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text('Historial de visitas por IPRESS'),
              trailing: Icon(Icons.download_rounded, color: Color(0xFF4F46E5)),
            ),
          ],
        ),
      ),
    );
  }
}
