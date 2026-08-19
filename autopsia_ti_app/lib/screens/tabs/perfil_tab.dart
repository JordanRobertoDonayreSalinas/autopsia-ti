import 'package:flutter/material.dart';

/// Pestaña "Mi Perfil": datos del auditor y acciones de sesión/sincronización.
class PerfilTab extends StatelessWidget {
  final String userName;
  final bool isSyncing;
  final VoidCallback onSync;
  final VoidCallback onLogout;

  const PerfilTab({
    super.key,
    required this.userName,
    required this.isSyncing,
    required this.onSync,
    required this.onLogout,
  });

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
            const Text('Perfil de Usuario Auditor', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
            const SizedBox(height: 16),
            ListTile(
              leading: const CircleAvatar(backgroundColor: Color(0xFF4F46E5), child: Icon(Icons.person, color: Colors.white)),
              title: Text(userName, style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: const Text('Rol: Administrador TI / Auditor de Campo\nEmpresa: System Perú Digital'),
            ),
            const Divider(),
            const SizedBox(height: 10),
            Row(
              children: [
                ElevatedButton.icon(
                  onPressed: onSync,
                  icon: const Icon(Icons.sync_rounded),
                  label: Text(isSyncing ? 'Sincronizando...' : 'Sincronizar Manualmente'),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF15803D), foregroundColor: Colors.white),
                ),
                const SizedBox(width: 12),
                OutlinedButton.icon(
                  onPressed: onLogout,
                  icon: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444)),
                  label: const Text('Cerrar Sesión', style: TextStyle(color: Color(0xFFEF4444))),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
