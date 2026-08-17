import 'package:flutter/material.dart';
import 'models/establecimiento.dart';
import 'services/offline_db_service.dart';
import 'services/sync_service.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const AutopsiaTiApp());
}

class AutopsiaTiApp extends StatelessWidget {
  const AutopsiaTiApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Actas de Diagnóstico Situacional - Autopsia TI',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: const Color(0xFFF1F5F9), // slate-100
        fontFamily: 'Roboto',
      ),
      home: const MainCampoScreen(),
    );
  }
}

class MainCampoScreen extends StatefulWidget {
  const MainCampoScreen({super.key});

  @override
  State<MainCampoScreen> createState() => _MainCampoScreenState();
}

class _MainCampoScreenState extends State<MainCampoScreen> {
  final SyncService _syncService = SyncService();
  bool _isOnline = false;
  bool _isSyncing = false;
  int _totalIpress = 524;
  List<Establecimiento> _searchResult = [];
  final TextEditingController _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _checkStatus();
  }

  Future<void> _checkStatus() async {
    final ver = await _syncService.checkVersion();
    setState(() {
      _isOnline = ver['success'] == true;
    });

    if (_isOnline) {
      _autoSync();
    }
  }

  Future<void> _autoSync() async {
    if (_isSyncing) return;
    setState(() => _isSyncing = true);
    await _syncService.descargarCatalogo();
    await _syncService.sincronizarPendientes();
    setState(() => _isSyncing = false);
  }

  void _onSearch(String term) async {
    if (term.isEmpty) {
      setState(() => _searchResult = []);
      return;
    }
    final res = await OfflineDbService.instance.buscarEstablecimientos(term);
    setState(() => _searchResult = res);
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isDesktop = size.width > 900;

    return Scaffold(
      body: Row(
        children: [
          // BARRA LATERAL OSCURA (SLATE 900) IGUAL A LA WEB
          if (isDesktop)
            Container(
              width: 260,
              color: const Color(0xFF0F172A), // slate-900
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header Logo
                  Container(
                    padding: const EdgeInsets.all(20),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFF4F46E5),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.medical_services_rounded, color: Colors.white, size: 24),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: const [
                            Text(
                              'Autopsia TI',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                            ),
                            Text(
                              'App de Campo Offline',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Divider(color: Color(0xFF1E293B)),
                  const SizedBox(height: 10),
                  // Secciones del Menú Lateral
                  _buildMenuItem(Icons.dashboard_rounded, 'Dashboard', false),
                  _buildMenuItem(Icons.assignment_rounded, 'Actas Diagnóstico', true),
                  _buildMenuItem(Icons.meeting_room_rounded, 'Actas de Reunión', false),
                  _buildMenuItem(Icons.person_rounded, 'Mi Perfil', false),
                  const Spacer(),
                  // Tarjeta inferior de estado
                  Container(
                    margin: const EdgeInsets.all(16),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFF334155)),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          backgroundColor: const Color(0xFF4F46E5),
                          child: const Text('JD', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: const [
                              Text('JORDAN ROBERTO', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                              Text('Administrador', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // CONTENIDO PRINCIPAL
          Expanded(
            child: Column(
              children: [
                // BARRA SUPERIOR HEADER
                Container(
                  height: 64,
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  color: Colors.white,
                  child: Row(
                    children: [
                      Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text(
                            'Actas de Diagnóstico Situacional',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                          ),
                          Text(
                            'Operaciones • Panel de Control de Monitoreo',
                            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                      const Spacer(),
                      // Insignia de Estado Red
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: _isOnline ? const Color(0xFFDCFCE7) : const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: _isOnline ? const Color(0xFF86EFAC) : const Color(0xFFFDE68A)),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              _isOnline ? Icons.wifi : Icons.wifi_off,
                              color: _isOnline ? const Color(0xFF15803D) : const Color(0xFFB45309),
                              size: 16,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              _isOnline ? 'Online' : 'Modo Campo Offline',
                              style: TextStyle(
                                color: _isOnline ? const Color(0xFF15803D) : const Color(0xFFB45309),
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // BODY SCROLLABLE
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // TARJETA DE GRADIENTE AZUL IGUAL A LA WEB LARAVEL
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF1D4ED8), Color(0xFF4F46E5)], // blue-700 to indigo-600
                              begin: Alignment.centerLeft,
                              end: Alignment.centerRight,
                            ),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF4F46E5).withOpacity(0.3),
                                blurRadius: 15,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              // Tarjetas de contadores de actas
                              Wrap(
                                spacing: 12,
                                children: [
                                  _buildStatCard('TOTAL', '$_totalIpress', const Color(0xFF0F172A), Colors.white),
                                  _buildStatCard('FIRMADAS', '524', const Color(0xFF065F46).withOpacity(0.4), const Color(0xFF34D399)),
                                  _buildStatCard('PENDIENTES', '0', const Color(0xFF92400E).withOpacity(0.4), const Color(0xFFFBBF24)),
                                  _buildStatCard('ANULADAS', '0', const Color(0xFF0F172A), const Color(0xFF94A3B8)),
                                ],
                              ),
                              const Spacer(),
                              // Botón Morado de + Nueva Acta
                              ElevatedButton.icon(
                                onPressed: () {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Abriendo formulario de Nueva Acta Offline...')),
                                  );
                                },
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
                                const Text(
                                  'Buscar Establecimiento (IPRESS) Offline',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                ),
                                const SizedBox(height: 4),
                                const Text(
                                  'Consulte cualquiera de los 524 establecimientos guardados en el disco local.',
                                  style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                ),
                                const SizedBox(height: 16),
                                TextField(
                                  controller: _searchCtrl,
                                  onChanged: _onSearch,
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
                        _searchResult.isEmpty
                            ? Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(40),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Column(
                                  children: const [
                                    Icon(Icons.search_off_rounded, size: 48, color: Color(0xFFCBD5E1)),
                                    SizedBox(height: 12),
                                    Text(
                                      'Escriba en el buscador superior para consultar las IPRESS en SQLite.',
                                      style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                              )
                            : ListView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: _searchResult.length,
                                itemBuilder: (context, index) {
                                  final item = _searchResult[index];
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
                                        '${item.distrito} - ${item.provincia} | Código UNICO: ${item.codigo}',
                                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                                      ),
                                      trailing: ElevatedButton.icon(
                                        onPressed: () {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(content: Text('Iniciando evaluación para ${item.nombre}')),
                                          );
                                        },
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
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(IconData icon, String title, bool active) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: active ? const Color(0xFF1E293B) : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
        border: active ? Border.all(color: const Color(0xFF334155)) : null,
      ),
      child: ListTile(
        leading: Icon(icon, color: active ? const Color(0xFF818CF8) : const Color(0xFF94A3B8), size: 20),
        title: Text(
          title,
          style: TextStyle(
            color: active ? Colors.white : const Color(0xFF94A3B8),
            fontWeight: active ? FontWeight.bold : FontWeight.normal,
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color bg, Color textColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: textColor.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Text(value, style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(color: textColor.withOpacity(0.8), fontSize: 9, fontWeight: FontWeight.bold, letterSpacing: 1)),
        ],
      ),
    );
  }
}
