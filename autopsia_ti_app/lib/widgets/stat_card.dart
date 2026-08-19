import 'package:flutter/material.dart';

/// Tarjeta pequeña de estadística (usada en la cabecera de la pestaña de Actas).
class StatCard extends StatelessWidget {
  final String label;
  final String value;
  final Color bg;
  final Color textColor;

  const StatCard({
    super.key,
    required this.label,
    required this.value,
    required this.bg,
    required this.textColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: textColor.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Text(value, style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(color: textColor.withValues(alpha: 0.8), fontSize: 9, fontWeight: FontWeight.bold, letterSpacing: 1)),
        ],
      ),
    );
  }
}
