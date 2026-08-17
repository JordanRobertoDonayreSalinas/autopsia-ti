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
      title: 'Autopsia TI Campo',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorSchemeSeed: const Color(0xFF4F46E5),
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
  int _pendingCount = 0;
  List<Establecimiento> _searchResult = [];
  final TextEditingController _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _checkStatus();
  }

  Future<void> _checkStatus() async {
    final ver = await _syncService.checkVersion();
    final pendientes = await OfflineDbService.instance.obtenerActasPendientes();
    setState(() {
      _isOnline = ver['success'] == true;
      _pendingCount = pendientes.length;
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
    final pendientes = await OfflineDbService.instance.obtenerActasPendientes();
    setState(() {
      _pendingCount = pendientes.length;
      _isSyncing = false;
    });
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
    final isDesktop = size.width > 800;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E1B4B),
        title: Row(
          children: [
            const Icon(Icons.medical_services_rounded, color: Colors.indigoAccent),
            const SizedBox(width: 10),
            const Text(
              'Autopsia TI - App de Campo v1.2.0',
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: _isOnline ? Colors.emerald.withOpacity(0.2) : Colors.amber.withOpacity(0.2),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: _isOnline ? Colors.emerald : Colors.amber),
              ),
              child: Row(
                children: [
                  Icon(
                    _isOnline ? Icons.wifi : Icons.wifi_off,
                    color: _isOnline ? Colors.emeraldAccent : Colors.amberAccent,
                    size: 16,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    _isOnline ? 'Online' : 'Modo Campo Offline',
                    style: TextStyle(
                      color: _isOnline ? Colors.emeraldAccent : Colors.amberAccent,
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
      body: Row(
        children: [
          if (isDesktop)
            NavigationRail(
              backgroundColor: const Color(0xFF0F172A),
              selectedIndex: 0,
              unselectedIconTheme: const IconThemeData(color: Colors.white70),
              selectedIconTheme: const IconThemeData(color: Colors.indigoAccent),
              destinations: const [
                NavigationRailDestination(
                  icon: Icon(Icons.assignment_add),
                  label: Text('Nueva Acta'),
                ),
                NavigationRailDestination(
                  icon: Icon(Icons.sync),
                  label: Text('Sincronizar'),
                ),
              ],
            ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Card(
                    elevation: 2,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Buscar Establecimiento (IPRESS) Offline',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          TextField(
                            controller: _searchCtrl,
                            onChanged: _onSearch,
                            decoration: InputDecoration(
                              hintText: 'Escriba código, nombre o distrito...',
                              prefixIcon: const Icon(Icons.search),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              filled: true,
                              fillColor: Colors.slate.shade50,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Expanded(
                    child: _searchResult.isEmpty
                        ? const Center(
                            child: Text(
                              'Escriba en el buscador para consultar las 524 IPRESS guardadas localmente.',
                              style: TextStyle(color: Colors.grey),
                            ),
                          )
                        : ListView.builder(
                            itemCount: _searchResult.length,
                            itemBuilder: (context, index) {
                              final item = _searchResult[index];
                              return Card(
                                margin: const EdgeInsets.only(bottom: 10),
                                child: ListTile(
                                  leading: CircleAvatar(
                                    backgroundColor: Colors.indigo.shade100,
                                    child: Text(item.categoria, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                                  ),
                                  title: Text(item.nombre, style: const TextStyle(fontWeight: FontWeight.bold)),
                                  subtitle: Text('${item.distrito} - ${item.provincia} | Código: ${item.codigo}'),
                                  trailing: ElevatedButton.icon(
                                    onPressed: () {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text('Iniciando evaluación para ${item.nombre}')),
                                      );
                                    },
                                    icon: const Icon(Icons.edit_document, size: 16),
                                    label: const Text('Evaluar'),
                                  ),
                                ),
                              );
                            },
                          ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
