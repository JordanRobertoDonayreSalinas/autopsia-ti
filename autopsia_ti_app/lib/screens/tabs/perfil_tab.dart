import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/sync_service.dart';

/// Pestaña "Mi Perfil" — espejo de resources/views/usuario/perfil/perfil.blade.php
/// (UsuarioController::perfil/perfilUpdate): edita nombres, apellidos, correo
/// y contraseña (opcional) del propio usuario. Requiere conexión para
/// guardar, igual que Gestionar Usuarios. Al final de la pantalla se agregan
/// "Sincronizar Manualmente" y "Cerrar Sesión" — acciones propias de la app
/// de campo que no existen en la página web, así que van claramente
/// separadas del formulario que sí es un espejo exacto.
class PerfilTab extends StatefulWidget {
  final String userName;
  final bool isSyncing;
  final VoidCallback onSync;
  final VoidCallback onLogout;
  final VoidCallback onProfileUpdated;

  const PerfilTab({
    super.key,
    required this.userName,
    required this.isSyncing,
    required this.onSync,
    required this.onLogout,
    required this.onProfileUpdated,
  });

  @override
  State<PerfilTab> createState() => _PerfilTabState();
}

class _PerfilTabState extends State<PerfilTab> {
  final _formKey = GlobalKey<FormState>();
  final _nombresCtrl = TextEditingController();
  final _apellidoPaternoCtrl = TextEditingController();
  final _apellidoMaternoCtrl = TextEditingController();
  final _correoCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();

  String _username = '';
  String? _updatedAt;
  bool _cargando = true;
  bool _guardando = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _cargarDesdeCache();
  }

  Future<void> _cargarDesdeCache() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() {
      _nombresCtrl.text = prefs.getString('user_nombres') ?? '';
      _apellidoPaternoCtrl.text = prefs.getString('user_apellido_paterno') ?? '';
      _apellidoMaternoCtrl.text = prefs.getString('user_apellido_materno') ?? '';
      _correoCtrl.text = prefs.getString('user_correo') ?? '';
      _username = prefs.getString('user_email') ?? ''; // 'user_email' guarda el DNI/username (ver LoginScreen)
      _updatedAt = prefs.getString('user_updated_at');
      _cargando = false;
    });
  }

  @override
  void dispose() {
    _nombresCtrl.dispose();
    _apellidoPaternoCtrl.dispose();
    _apellidoMaternoCtrl.dispose();
    _correoCtrl.dispose();
    _passwordCtrl.dispose();
    _passwordConfirmCtrl.dispose();
    super.dispose();
  }

  String _ultimaActualizacion() {
    if (_updatedAt == null || _updatedAt!.isEmpty) return 'Sin datos';
    final fecha = DateTime.tryParse(_updatedAt!);
    if (fecha == null) return 'Sin datos';
    final diff = DateTime.now().difference(fecha);
    if (diff.inMinutes < 1) return 'hace un momento';
    if (diff.inMinutes < 60) return 'hace ${diff.inMinutes} minuto(s)';
    if (diff.inHours < 24) return 'hace ${diff.inHours} hora(s)';
    if (diff.inDays < 30) return 'hace ${diff.inDays} día(s)';
    return '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}';
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _guardando = true;
      _error = null;
    });

    final payload = <String, dynamic>{
      'name': _nombresCtrl.text.trim(),
      'apellido_paterno': _apellidoPaternoCtrl.text.trim(),
      'apellido_materno': _apellidoMaternoCtrl.text.trim(),
      'email': _correoCtrl.text.trim(),
    };
    if (_passwordCtrl.text.isNotEmpty) {
      payload['password'] = _passwordCtrl.text;
      payload['password_confirmation'] = _passwordConfirmCtrl.text;
    }

    final res = await SyncService().actualizarPerfil(payload);
    if (!mounted) return;
    setState(() => _guardando = false);

    if (res['success'] == true) {
      final user = res['user'] as Map<String, dynamic>? ?? {};
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_name', user['nombre_completo'] ?? widget.userName);
      await prefs.setString('user_nombres', user['name'] ?? _nombresCtrl.text.trim());
      await prefs.setString('user_apellido_paterno', user['apellido_paterno'] ?? _apellidoPaternoCtrl.text.trim());
      await prefs.setString('user_apellido_materno', user['apellido_materno'] ?? _apellidoMaternoCtrl.text.trim());
      await prefs.setString('user_correo', user['email'] ?? _correoCtrl.text.trim());
      await prefs.setString('user_updated_at', user['updated_at'] ?? '');

      _passwordCtrl.clear();
      _passwordConfirmCtrl.clear();
      if (!mounted) return;
      setState(() => _updatedAt = user['updated_at']);
      widget.onProfileUpdated();

      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: const Color(0xFF15803D),
        content: Text(res['message'] ?? 'Tu perfil ha sido actualizado correctamente.'),
      ));
    } else {
      final errors = res['errors'] as Map<String, dynamic>?;
      setState(() => _error = errors != null
          ? errors.values.map((v) => (v as List).first).join('\n')
          : (res['message'] ?? 'No se pudo actualizar el perfil.'));
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()));
    }

    final nombreCompleto = widget.userName;
    final inicial = _nombresCtrl.text.isNotEmpty ? _nombresCtrl.text[0].toUpperCase() : 'U';

    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Encabezado visual (espejo del bloque de cabecera de perfil.blade.php)
            Row(
              children: [
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(color: const Color(0xFF059669), borderRadius: BorderRadius.circular(20)),
                  alignment: Alignment.center,
                  child: Text(inicial, style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w900)),
                ),
                const SizedBox(width: 20),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(nombreCompleto, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF1E293B))),
                      const SizedBox(height: 6),
                      Wrap(
                        spacing: 8,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(20)),
                            child: Text('DNI: $_username', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(20)),
                            child: const Text('Rol: Usuario', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF047857))),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 28),

            Form(
              key: _formKey,
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

                  Row(children: [
                    Container(width: 4, height: 18, decoration: BoxDecoration(color: const Color(0xFF10B981), borderRadius: BorderRadius.circular(4))),
                    const SizedBox(width: 8),
                    const Text('INFORMACIÓN PERSONAL', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF1E293B), letterSpacing: 0.5)),
                  ]),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _nombresCtrl,
                    decoration: const InputDecoration(labelText: 'Nombres', isDense: true),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                    onChanged: (_) => setState(() {}),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _apellidoPaternoCtrl,
                          decoration: const InputDecoration(labelText: 'Apellido Paterno', isDense: true),
                          validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: TextFormField(
                          controller: _apellidoMaternoCtrl,
                          decoration: const InputDecoration(labelText: 'Apellido Materno', isDense: true),
                          validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _correoCtrl,
                    decoration: const InputDecoration(labelText: 'Correo Electrónico', isDense: true),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                  ),

                  const SizedBox(height: 28),
                  Row(children: [
                    Container(width: 4, height: 18, decoration: BoxDecoration(color: const Color(0xFFF59E0B), borderRadius: BorderRadius.circular(4))),
                    const SizedBox(width: 8),
                    const Text('SEGURIDAD', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF1E293B), letterSpacing: 0.5)),
                  ]),
                  const SizedBox(height: 4),
                  const Padding(
                    padding: EdgeInsets.only(left: 12),
                    child: Text('Deje estos campos vacíos para mantener su contraseña actual.', style: TextStyle(fontSize: 11, color: Color(0xFFB45309), fontStyle: FontStyle.italic)),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _passwordCtrl,
                          obscureText: true,
                          decoration: const InputDecoration(labelText: 'Nueva Contraseña', hintText: 'Mínimo 8 caracteres', isDense: true),
                          validator: (v) => (v != null && v.isNotEmpty && v.length < 8) ? 'Mínimo 8 caracteres' : null,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: TextFormField(
                          controller: _passwordConfirmCtrl,
                          obscureText: true,
                          decoration: const InputDecoration(labelText: 'Confirmar Contraseña', isDense: true),
                          validator: (v) => (_passwordCtrl.text.isNotEmpty && v != _passwordCtrl.text) ? 'No coincide' : null,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: Row(
                          children: [
                            const Icon(Icons.schedule_rounded, size: 14, color: Color(0xFF94A3B8)),
                            const SizedBox(width: 6),
                            Text('Última actualización: ${_ultimaActualizacion()}', style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8), fontStyle: FontStyle.italic)),
                          ],
                        ),
                      ),
                      ElevatedButton.icon(
                        onPressed: _guardando ? null : _guardar,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: _guardando
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.save_rounded, size: 18),
                        label: const Text('Guardar Cambios', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const Divider(height: 48),

            // --- Acciones propias de la app de campo (no existen en la web) ---
            const Text('APP DE CAMPO', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.8)),
            const SizedBox(height: 10),
            Wrap(
              spacing: 12,
              runSpacing: 8,
              children: [
                ElevatedButton.icon(
                  onPressed: widget.onSync,
                  icon: const Icon(Icons.sync_rounded),
                  label: Text(widget.isSyncing ? 'Sincronizando...' : 'Sincronizar Manualmente'),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF15803D), foregroundColor: Colors.white),
                ),
                OutlinedButton.icon(
                  onPressed: widget.onLogout,
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
