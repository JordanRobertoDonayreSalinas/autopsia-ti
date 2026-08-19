import 'package:flutter/material.dart';
import '../../models/establecimiento.dart';
import '../../ui/views/renipress_modal.dart';
import '../../widgets/stat_card.dart';

/// Pestaña "Actas de Diagnóstico Situacional": contadores, buscador de IPRESS
/// y disparador del diálogo de nueva acta.
class ActasDiagnosticoTab extends StatelessWidget {
  final int totalIpress;
  final int firmadas;
  final int pendientesCount;
  final int anuladas;
  final List<Establecimiento> searchResult;
  final TextEditingController searchCtrl;
  final ValueChanged<String> onSearch;
  final void Function(Establecimiento? item) onNuevaActa;

  const ActasDiagnosticoTab({
    super.key,
    required this.totalIpress,
    required this.firmadas,
    required this.pendientesCount,
    required this.anuladas,
    required this.searchResult,
    required this.searchCtrl,
    required this.onSearch,
    required this.onNuevaActa,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // TARJETA DE GRADIENTE AZUL
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1D4ED8), Color(0xFF4F46E5)],
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF4F46E5).withValues(alpha: 0.3),
                blurRadius: 15,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            runSpacing: 16,
            spacing: 16,
            children: [
              // Tarjetas de contadores de actas
              Wrap(
                spacing: 12,
                runSpacing: 8,
                children: [
                  StatCard(label: 'TOTAL IPRESS', value: '$totalIpress', bg: const Color(0xFF0F172A), textColor: Colors.white),
                  StatCard(label: 'FIRMADAS', value: '$firmadas', bg: const Color(0xFF065F46).withValues(alpha: 0.4), textColor: const Color(0xFF34D399)),
                  StatCard(label: 'PENDIENTES', value: '$pendientesCount', bg: const Color(0xFF92400E).withValues(alpha: 0.4), textColor: const Color(0xFFFBBF24)),
                  StatCard(label: 'ANULADAS', value: '$anuladas', bg: const Color(0xFF0F172A), textColor: const Color(0xFF94A3B8)),
                ],
              ),
              // Botón Morado de + Nueva Acta
              ElevatedButton.icon(
                onPressed: () => onNuevaActa(null),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4338CA),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 4,
                ),
                icon: const Icon(Icons.add_rounded, size: 20),
                label: const Text('Nueva Acta', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              ),
            ],
          ),
        ),

        const SizedBox(height: 24),

        // BUSCADOR Y LISTA DE CONSULTORIOS E IPRESS
        Card(
          elevation: 0,
          color: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: Color(0xFFE2E8F0)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Buscar Establecimiento (IPRESS) Offline',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Consulte establecimientos guardados en disco local o busque por código de 8 dígitos SUSALUD.',
                            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    OutlinedButton.icon(
                      onPressed: () => showDialog(context: context, builder: (_) => const RenipressModal()),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Color(0xFF4F46E5)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: const Icon(Icons.verified_rounded, color: Color(0xFF4F46E5), size: 18),
                      label: const Text('Consultar RENIPRESS', style: TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 13)),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: searchCtrl,
                  onChanged: onSearch,
                  decoration: InputDecoration(
                    hintText: 'Escriba código, nombre o distrito de la clínica/hospital...',
                    prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF64748B)),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF4F46E5), width: 2),
                    ),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                  ),
                ),
              ],
            ),
          ),
        ),

        const SizedBox(height: 20),

        // RESULTADOS DE BÚSQUEDA / TABLA
        searchResult.isEmpty
            ? Container(
                width: double.infinity,
                padding: const EdgeInsets.all(40),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: const Column(
                  children: [
                    Icon(Icons.search_off_rounded, size: 48, color: Color(0xFFCBD5E1)),
                    SizedBox(height: 12),
                    Text(
                      'No se encontraron establecimientos con ese criterio.',
                      style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              )
            : ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: searchResult.length,
                itemBuilder: (context, index) {
                  final item = searchResult[index];
                  return Card(
                    elevation: 0,
                    color: Colors.white,
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                      side: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                      leading: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEEF2FF),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFC7D2FE)),
                        ),
                        child: Text(
                          item.categoria,
                          style: const TextStyle(color: Color(0xFF3730A3), fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ),
                      title: Text(item.nombre, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                      subtitle: Text(
                        '${item.distrito} - ${item.provincia} | Código UNICO: ${item.codigo}\nDirección: ${item.direccion ?? 'No registrada'}',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                      ),
                      trailing: ElevatedButton.icon(
                        onPressed: () => onNuevaActa(item),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4F46E5),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        icon: const Icon(Icons.edit_note_rounded, size: 18),
                        label: const Text('Iniciar Acta'),
                      ),
                    ),
                  );
                },
              ),
      ],
    );
  }
}
