import 'package:flutter/material.dart';

/// Pestaña "Gestionar Usuarios": lista los usuarios reales obtenidos de
/// GET /api/v1/users. Sin conexión (o sin caché) muestra un estado vacío
/// en vez de datos de demostración.
class GestionarUsuariosTab extends StatelessWidget {
  final List<Map<String, dynamic>> realUsers;

  const GestionarUsuariosTab({super.key, required this.realUsers});

  @override
  Widget build(BuildContext context) {
    final usuarios = realUsers.map((u) {
      final nombreCompleto = u['nombre_completo'] ?? '${u['apellido_paterno']} ${u['apellido_materno']} ${u['nombres']}';
      final initial = u['nombres'] != null && u['nombres'].toString().isNotEmpty ? u['nombres'].toString()[0] : 'U';
      return {
        'initial': initial,
        'nombre': nombreCompleto,
        'id': u['id'].toString(),
        'usuario': u['username'] ?? '',
        'rol': (u['role'] ?? 'USER').toString().toUpperCase(),
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
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Formulario de Nuevo Usuario')),
                    );
                  },
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
                        const DataCell(
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.circle, color: Color(0xFF4F46E5), size: 8),
                              SizedBox(width: 6),
                              Text('ACTIVO', style: TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 10)),
                            ],
                          ),
                        ),
                        DataCell(
                          IconButton(
                            icon: const Icon(Icons.edit_outlined, color: Color(0xFF94A3B8), size: 18),
                            onPressed: () {},
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
