import 'package:flutter/material.dart';
import '../../services/sync_service.dart';

/// Roles y estados reales de Laravel (AdminController), no inventados.
const _roles = {
  'admin': 'Administrador',
  'operador': 'Operador (Monitoreo)',
  'visor_cronograma': 'Visor Cronograma (Solo lectura)',
  'user': 'Usuario',
};

/// Pestaña "Gestionar Usuarios": lista los usuarios reales obtenidos de
/// GET /api/v1/users, y permite Crear/Editar/Activar-Bloquear contra los
/// endpoints nuevos de UsersApiController (espejo de AdminController).
class GestionarUsuariosTab extends StatelessWidget {
  final List<Map<String, dynamic>> realUsers;
  final SyncService syncService;
  final VoidCallback onChanged;

  const GestionarUsuariosTab({
    super.key,
    required this.realUsers,
    required this.syncService,
    required this.onChanged,
  });

  Future<void> _toggleStatus(BuildContext context, Map<String, dynamic> u) async {
    final res = await syncService.toggleUsuarioStatus(u['id'] as int);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: res['success'] == true ? const Color(0xFF15803D) : const Color(0xFFB91C1C),
        content: Text(res['message'] ?? (res['success'] == true ? 'Estado actualizado.' : 'No se pudo actualizar el estado.')),
      ),
    );
    if (res['success'] == true) onChanged();
  }

  void _abrirFormulario(BuildContext context, {Map<String, dynamic>? usuario}) {
    showDialog(
      context: context,
      builder: (_) => _UsuarioFormDialog(
        syncService: syncService,
        usuario: usuario,
        onSaved: onChanged,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final usuarios = realUsers.map((u) {
      final nombreCompleto = u['nombre_completo'] ?? '${u['apellido_paterno']} ${u['apellido_materno']} ${u['nombres']}';
      final initial = u['nombres'] != null && u['nombres'].toString().isNotEmpty ? u['nombres'].toString()[0] : 'U';
      return {
        'raw': u,
        'initial': initial,
        'nombre': nombreCompleto,
        'id': u['id'].toString(),
        'usuario': u['username'] ?? '',
        'rol': (_roles[u['role']] ?? (u['role'] ?? 'USUARIO').toString()).toUpperCase(),
        'activo': u['status'] != 'inactive',
      };
    }).toList();

    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: const BorderSide(color: Color(0xFFE2E8F0))),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Text(
                  'Listado de Usuarios',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                ),
                const Spacer(),
                ElevatedButton.icon(
                  onPressed: () => _abrirFormulario(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('Nuevo Usuario', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                ),
              ],
            ),
            const SizedBox(height: 24),

            if (usuarios.isEmpty)
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
                    Icon(Icons.cloud_off_rounded, size: 40, color: Color(0xFFCBD5E1)),
                    SizedBox(height: 10),
                    Text(
                      'No hay usuarios sincronizados todavía.',
                      style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Conéctese a internet para descargar el listado real desde el servidor.',
                      style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              )
            else
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowHeight: 44,
                  dataRowMinHeight: 64,
                  dataRowMaxHeight: 64,
                  horizontalMargin: 12,
                  columnSpacing: 36,
                  headingTextStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.8),
                  columns: const [
                    DataColumn(label: Text('NOMBRES')),
                    DataColumn(label: Text('USUARIO')),
                    DataColumn(label: Text('ROL')),
                    DataColumn(label: Text('ESTADO DE CUENTA')),
                    DataColumn(label: Text('ACCIONES')),
                  ],
                  rows: usuarios.map((u) {
                    final activo = u['activo'] == true;
                    return DataRow(
                      cells: [
                        DataCell(
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircleAvatar(
                                radius: 18,
                                backgroundColor: const Color(0xFF3B82F6),
                                child: Text(
                                  u['initial'].toString(),
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    u['nombre'].toString(),
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontSize: 13),
                                  ),
                                  Text(
                                    'ID: ${u['id']}',
                                    style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEEF2FF),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              u['usuario'].toString(),
                              style: const TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                          ),
                        ),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF3E8FF),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              u['rol'].toString(),
                              style: const TextStyle(color: Color(0xFF7E22CE), fontWeight: FontWeight.w800, fontSize: 10),
                            ),
                          ),
                        ),
                        DataCell(
                          InkWell(
                            onTap: () => _toggleStatus(context, u['raw'] as Map<String, dynamic>),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.circle, color: activo ? const Color(0xFF4F46E5) : const Color(0xFF94A3B8), size: 8),
                                const SizedBox(width: 6),
                                Text(
                                  activo ? 'ACTIVO' : 'BLOQUEADO',
                                  style: TextStyle(color: activo ? const Color(0xFF4F46E5) : const Color(0xFF94A3B8), fontWeight: FontWeight.bold, fontSize: 10),
                                ),
                                const SizedBox(width: 4),
                                const Icon(Icons.sync_alt_rounded, size: 12, color: Color(0xFFCBD5E1)),
                              ],
                            ),
                          ),
                        ),
                        DataCell(
                          IconButton(
                            icon: const Icon(Icons.edit_outlined, color: Color(0xFF94A3B8), size: 18),
                            onPressed: () => _abrirFormulario(context, usuario: u['raw'] as Map<String, dynamic>),
                          ),
                        ),
                      ],
                    );
                  }).toList(),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

/// Formulario de Crear/Editar usuario — mismos campos y validaciones que
/// AdminController::usersStore/usersUpdate (ver informe de auditoría).
class _UsuarioFormDialog extends StatefulWidget {
  final SyncService syncService;
  final Map<String, dynamic>? usuario; // null = creación
  final VoidCallback onSaved;

  const _UsuarioFormDialog({required this.syncService, this.usuario, required this.onSaved});

  @override
  State<_UsuarioFormDialog> createState() => _UsuarioFormDialogState();
}

class _UsuarioFormDialogState extends State<_UsuarioFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _apellidoPaternoCtrl;
  late final TextEditingController _apellidoMaternoCtrl;
  late final TextEditingController _nameCtrl;
  late final TextEditingController _usernameCtrl;
  late final TextEditingController _emailCtrl;
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();
  late String _role;
  late String _status;
  bool _guardando = false;
  bool _verificandoDni = false;
  String? _errorGeneral;

  bool get _esEdicion => widget.usuario != null;

  @override
  void initState() {
    super.initState();
    final u = widget.usuario;
    _apellidoPaternoCtrl = TextEditingController(text: u?['apellido_paterno'] ?? '');
    _apellidoMaternoCtrl = TextEditingController(text: u?['apellido_materno'] ?? '');
    _nameCtrl = TextEditingController(text: u?['name'] ?? u?['nombres'] ?? '');
    _usernameCtrl = TextEditingController(text: u?['username'] ?? '');
    _emailCtrl = TextEditingController(text: u?['email'] ?? '');
    _role = u?['role'] ?? 'user';
    _status = u?['status'] ?? 'active';
  }

  @override
  void dispose() {
    _apellidoPaternoCtrl.dispose();
    _apellidoMaternoCtrl.dispose();
    _nameCtrl.dispose();
    _usernameCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _passwordConfirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _verificarDni() async {
    final doc = _usernameCtrl.text.trim();
    if (doc.length != 8) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ingrese un DNI de 8 dígitos para verificar.')));
      return;
    }
    setState(() => _verificandoDni = true);
    final res = await widget.syncService.buscarDni('DNI', doc);
    setState(() => _verificandoDni = false);
    if (!mounted) return;

    if (res['existing_user'] != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: const Color(0xFFB91C1C),
        content: Text('Este documento ya pertenece a un usuario del sistema: ${res['existing_user']['nombre']}'),
      ));
      return;
    }
    if (res['exists'] == true || res['exists_external'] == true) {
      setState(() {
        _apellidoPaternoCtrl.text = res['apellido_paterno'] ?? '';
        _apellidoMaternoCtrl.text = res['apellido_materno'] ?? '';
        _nameCtrl.text = res['nombres'] ?? '';
        if ((res['email'] ?? '').toString().isNotEmpty) _emailCtrl.text = res['email'];
      });
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        backgroundColor: Color(0xFF15803D),
        content: Text('Datos autocompletados.'),
      ));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No se encontró información para ese documento.')));
    }
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _guardando = true;
      _errorGeneral = null;
    });

    final payload = <String, dynamic>{
      'name': _nameCtrl.text.trim(),
      'apellido_paterno': _apellidoPaternoCtrl.text.trim(),
      'apellido_materno': _apellidoMaternoCtrl.text.trim(),
      'username': _usernameCtrl.text.trim(),
      'email': _emailCtrl.text.trim(),
      'role': _role,
      'status': _status,
    };
    if (_passwordCtrl.text.isNotEmpty) {
      payload['password'] = _passwordCtrl.text;
      payload['password_confirmation'] = _passwordConfirmCtrl.text;
    }

    final res = _esEdicion
        ? await widget.syncService.actualizarUsuario(widget.usuario!['id'] as int, payload)
        : await widget.syncService.crearUsuario(payload);

    if (!mounted) return;
    setState(() => _guardando = false);

    if (res['success'] == true) {
      Navigator.pop(context);
      widget.onSaved();
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: const Color(0xFF15803D),
        content: Text(res['message'] ?? 'Usuario guardado correctamente.'),
      ));
    } else {
      final errors = res['errors'] as Map<String, dynamic>?;
      setState(() => _errorGeneral = errors != null
          ? errors.values.map((v) => (v as List).first).join('\n')
          : (res['message'] ?? 'No se pudo guardar el usuario.'));
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Text(_esEdicion ? 'Editar Usuario' : 'Nuevo Usuario', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
      content: SizedBox(
        width: 480,
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (_errorGeneral != null)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(color: const Color(0xFFFEE2E2), borderRadius: BorderRadius.circular(8)),
                    child: Text(_errorGeneral!, style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 12)),
                  ),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: _usernameCtrl,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(labelText: 'Usuario (DNI)', isDense: true),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                      ),
                    ),
                    if (!_esEdicion) ...[
                      const SizedBox(width: 8),
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: OutlinedButton(
                          onPressed: _verificandoDni ? null : _verificarDni,
                          child: _verificandoDni
                              ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                              : const Text('Verificar'),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _apellidoPaternoCtrl,
                  decoration: const InputDecoration(labelText: 'Apellido Paterno', isDense: true),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _apellidoMaternoCtrl,
                  decoration: const InputDecoration(labelText: 'Apellido Materno', isDense: true),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _nameCtrl,
                  decoration: const InputDecoration(labelText: 'Nombres', isDense: true),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _emailCtrl,
                  decoration: const InputDecoration(labelText: 'Correo electrónico', isDense: true),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _role,
                  decoration: const InputDecoration(labelText: 'Rol', isDense: true),
                  items: _roles.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))).toList(),
                  onChanged: (v) => setState(() => _role = v ?? _role),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _status,
                  decoration: const InputDecoration(labelText: 'Estado de cuenta', isDense: true),
                  items: const [
                    DropdownMenuItem(value: 'active', child: Text('ACTIVO')),
                    DropdownMenuItem(value: 'inactive', child: Text('BLOQUEADO')),
                  ],
                  onChanged: (v) => setState(() => _status = v ?? _status),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _passwordCtrl,
                  obscureText: true,
                  decoration: InputDecoration(labelText: _esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña', isDense: true),
                  validator: (v) {
                    if (!_esEdicion && (v == null || v.length < 6)) return 'Mínimo 6 caracteres';
                    if (_esEdicion && v != null && v.isNotEmpty && v.length < 6) return 'Mínimo 6 caracteres';
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _passwordConfirmCtrl,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Confirmar contraseña', isDense: true),
                  validator: (v) {
                    if (_passwordCtrl.text.isNotEmpty && v != _passwordCtrl.text) return 'No coincide';
                    return null;
                  },
                ),
              ],
            ),
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancelar')),
        ElevatedButton(
          onPressed: _guardando ? null : _guardar,
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
          child: _guardando
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : Text(_esEdicion ? 'Guardar Cambios' : 'Crear Usuario'),
        ),
      ],
    );
  }
}
