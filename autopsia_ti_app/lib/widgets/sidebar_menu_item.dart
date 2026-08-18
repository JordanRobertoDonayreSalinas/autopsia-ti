import 'package:flutter/material.dart';

/// Ítem de navegación de la barra lateral (sidebar) de [MainCampoScreen].
class SidebarMenuItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final bool active;
  final VoidCallback onTap;
  final bool withChevron;

  const SidebarMenuItem({
    super.key,
    required this.icon,
    required this.title,
    required this.active,
    required this.onTap,
    this.withChevron = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 1),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 9),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF4F46E5) : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          children: [
            Icon(icon, color: active ? Colors.white : const Color(0xFF94A3B8), size: 17),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  color: active ? Colors.white : const Color(0xFFCBD5E1),
                  fontWeight: active ? FontWeight.w700 : FontWeight.w400,
                  fontSize: 12,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (withChevron)
              Icon(
                Icons.keyboard_arrow_down_rounded,
                color: active ? Colors.white70 : const Color(0xFF64748B),
                size: 15,
              ),
          ],
        ),
      ),
    );
  }
}
