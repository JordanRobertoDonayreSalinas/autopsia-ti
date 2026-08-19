import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_computo.dart';
import '../models/establecimiento.dart';
import '../models/monitoreo_modulo.dart';
import '../models/reunion.dart';
import '../repositories/acta_repository.dart';
import '../repositories/establecimiento_repository.dart';
import '../repositories/reunion_repository.dart';
import '../services/sync_service.dart';
import '../widgets/sidebar_menu_item.dart';
import 'acta_detalle_screen.dart';
import 'login_screen.dart';
import 'tabs/actas_diagnostico_tab.dart';
import 'tabs/dashboard_tab.dart';
import 'tabs/establecimientos_tab.dart';
import 'tabs/gestionar_usuarios_tab.dart';
import 'tabs/perfil_tab.dart';
import 'tabs/reportes_tab.dart';
import 'tabs/reuniones_tab.dart';

class MainCampoScreen extends StatefulWidget {
  const MainCampoScreen({super.key});

  @override
  State<MainCampoScreen> createState() => _MainCampoScreenState();
}

class _MainCampoScreenState extends State<MainCampoScreen> {
  final SyncService _syncService = SyncService();
  final EstablecimientoRepository _establecimientoRepo = EstablecimientoRepository();
  final ActaRepository _actaRepo = ActaRepository();
  final ReunionRepository _reunionRepo = ReunionRepository();

  bool _isOnline = false;
  bool _isSyncing = false;
  int _totalIpress = 0;
  int _sinDiagnostico = 0;
  int _conDiagnostico = 0;
  List<Map<String, dynamic>> _markers = [];
  List<Map<String, dynamic>> _realUsers = [];
  List<Reunion> _reuniones = [];
  int _pendientesCount = 0;
  String _selectedMenu = 'Dashboard';
  String _userName = 'JORDAN ROBERTO';
  List<Establecimiento> _searchResult = [];
  final TextEditingController _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _checkStatus();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final res = await _establecimientoRepo.buscar('');
    final actas = await _actaRepo.obtenerPendientes();
    final reuniones = await _reunionRepo.obtenerTodas();
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _searchResult = res;
      _pendientesCount = actas.length;
      _reuniones = reuniones;
      _userName = prefs.getString('user_name') ?? 'JORDAN ROBERTO';
    });

    // Cargar datos de la API de Laravel si está online
    if (_isOnline) {
      _fetchApiData();
    }
  }

  Future<void> _fetchApiData() async {
    try {
      final stats = await _syncService.getDashboardStats();
      final marks = await _syncService.getMapMarkers();
      final users = await _syncService.getUsers();

      setState(() {
        if (stats['success'] == true) {
          _totalIpress = stats['total_ipress'] ?? 0;
          _sinDiagnostico = stats['sin_diagnostico'] ?? 0;
          _conDiagnostico = stats['con_diagnostico'] ?? 0;
        }
        _markers = marks;
        _realUsers = users;
      });
    } catch (_) {}
  }

  Future<void> _checkStatus() async {
    final ver = await _syncService.checkVersion();
    setState(() {
      _isOnline = ver['success'] == true;
    });

    if (_isOnline) {
      _autoSync();
      _fetchApiData();
    }
  }

  Future<void> _autoSync() async {
    if (_isSyncing) return;
    setState(() => _isSyncing = true);
    await _establecimientoRepo.descargarCatalogo();
    final resultado = await _actaRepo.sincronizarPendientes();
    await _loadInitialData();
    setState(() => _isSyncing = false);

    if (!mounted) return;
    final sincronizados = resultado['sincronizados'] ?? 0;
    final errores = List<Map<String, dynamic>>.from(resultado['errores'] ?? []);
    if (errores.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFFB91C1C),
          content: Text(
            sincronizados > 0
                ? '$sincronizados acta(s) sincronizada(s). ${errores.length} con error: ${errores.first['message']}'
                : 'No se pudo sincronizar: ${errores.first['message']}',
          ),
          duration: const Duration(seconds: 6),
        ),
      );
    } else if (sincronizados > 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFF15803D),
          content: Text('$sincronizados acta(s) sincronizada(s) con el servidor.'),
        ),
      );
    }
  }

  Future<void> _cerrarSesion() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('is_logged_in', false);

    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginScreen()),
    );
  }

  void _onSearch(String term) async {
    final res = await _establecimientoRepo.buscar(term);
    setState(() => _searchResult = res);
  }

  void _mostrarDialogoNuevaActa(Establecimiento? itemSeleccionado) {
    Establecimiento? ipress = itemSeleccionado ?? (_searchResult.isNotEmpty ? _searchResult.first : null);
    final auditorCtrl = TextEditingController(text: _userName);
    final obsCtrl = TextEditingController();
    final equipoNombreCtrl = TextEditingController();
    final equipoSerieCtrl = TextEditingController();
    final pozoTierraCantCtrl = TextEditingController();
    final pozoTierraOpCtrl = TextEditingController();
    final panelSolarCantCtrl = TextEditingController();
    final panelSolarOpCtrl = TextEditingController();
    String pozoTierra = 'NO';
    String panelSolar = 'NO';

    Map<String, bool> consultorios = {
      'Triaje / Admisión': true,
      'Consultorio Medicina General': true,
      'Consultorio Odontología': false,
      'Emergencia / Utopic': true,
      'Farmacia / Almacén': false,
    };

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.note_add_rounded, color: Color(0xFF4F46E5)),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text(
                      'Nueva Acta de Diagnóstico TI',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                    ),
                  ),
                ],
              ),
              content: SizedBox(
                width: 600,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Establecimiento (IPRESS):', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFCBD5E1))),
                        child: Row(
                          children: [
                            const Icon(Icons.local_hospital_rounded, color: Color(0xFF4F46E5), size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                ipress != null ? '${ipress.nombre} (${ipress.codigo})' : 'Seleccione una IPRESS',
                                style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontSize: 13),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Auditor / Técnico Responsable:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      TextField(
                        controller: auditorCtrl,
                        decoration: InputDecoration(
                          prefixIcon: const Icon(Icons.person_outline_rounded, color: Color(0xFF64748B)),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Infraestructura Eléctrica:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      _CampoInstalacion(
                        label: 'Pozo a tierra',
                        valor: pozoTierra,
                        cantidadCtrl: pozoTierraCantCtrl,
                        operativosCtrl: pozoTierraOpCtrl,
                        onChanged: (v) => setDialogState(() => pozoTierra = v),
                      ),
                      const SizedBox(height: 10),
                      _CampoInstalacion(
                        label: 'Panel solar',
                        valor: panelSolar,
                        cantidadCtrl: panelSolarCantCtrl,
                        operativosCtrl: panelSolarOpCtrl,
                        onChanged: (v) => setDialogState(() => panelSolar = v),
                      ),
                      const SizedBox(height: 16),
                      const Text('Consultorios Evaluados en Campo:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      ...consultorios.keys.map((key) {
                        return CheckboxListTile(
                          dense: true,
                          contentPadding: EdgeInsets.zero,
                          title: Text(key, style: const TextStyle(fontSize: 13)),
                          value: consultorios[key],
                          onChanged: (val) {
                            setDialogState(() {
                              consultorios[key] = val ?? false;
                            });
                          },
                        );
                      }),
                      const SizedBox(height: 16),
                      const Text('Inventario Rápido de Equipo Principal:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Expanded(
                            flex: 2,
                            child: TextField(
                              controller: equipoNombreCtrl,
                              decoration: InputDecoration(
                                labelText: 'Descripción',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            flex: 1,
                            child: TextField(
                              controller: equipoSerieCtrl,
                              decoration: InputDecoration(
                                labelText: 'N° Serie',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      const Text('Observaciones Situacionales de Infraestructura:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                      const SizedBox(height: 6),
                      TextField(
                        controller: obsCtrl,
                        maxLines: 2,
                        decoration: InputDecoration(
                          hintText: 'Ingrese observaciones de red, cableado o equipos informáticos...',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar', style: TextStyle(color: Color(0xFF64748B))),
                ),
                ElevatedButton.icon(
                  onPressed: () async {
                    if (ipress == null || ipress.id == null) return;
                    final offlineId = 'ACTA-${DateTime.now().millisecondsSinceEpoch}';
                    final ahora = DateTime.now();
                    final responsable = auditorCtrl.text.trim().isEmpty ? _userName : auditorCtrl.text.trim();

                    final acta = CabeceraMonitoreo(
                      offlineId: offlineId,
                      localCreatedAt: ahora.toIso8601String(),
                      establecimientoId: ipress.id!,
                      fecha: ahora.toString().split(' ')[0],
                      responsable: responsable,
                      implementador: responsable,
                      pozoTierra: pozoTierra,
                      pozoTierraCantidad: pozoTierra == 'SI' ? int.tryParse(pozoTierraCantCtrl.text) : null,
                      pozoTierraOperativos: pozoTierra == 'SI' ? int.tryParse(pozoTierraOpCtrl.text) : null,
                      panelSolar: panelSolar,
                      panelSolarCantidad: panelSolar == 'SI' ? int.tryParse(panelSolarCantCtrl.text) : null,
                      panelSolarOperativos: panelSolar == 'SI' ? int.tryParse(panelSolarOpCtrl.text) : null,
                    );

                    // Un módulo (consultorio) por cada casilla marcada, con la
                    // observación capturada como contenido inicial. Ver Fase 5
                    // del plan para las fichas completas por módulo.
                    final consultoriosMarcados = consultorios.entries.where((e) => e.value).map((e) => e.key).toList();
                    final modulos = consultoriosMarcados
                        .map((nombre) => MonitoreoModulo(
                              actaOfflineId: offlineId,
                              moduloNombre: nombre,
                              contenido: jsonEncode({'observaciones': obsCtrl.text.trim()}),
                            ))
                        .toList();

                    final equipos = <EquipoComputo>[];
                    if (equipoNombreCtrl.text.trim().isNotEmpty) {
                      final moduloEquipo = consultoriosMarcados.isNotEmpty ? consultoriosMarcados.first : 'GENERAL';
                      equipos.add(EquipoComputo(
                        actaOfflineId: offlineId,
                        modulo: moduloEquipo,
                        descripcion: equipoNombreCtrl.text.trim(),
                        nroSerie: equipoSerieCtrl.text.trim().isEmpty ? null : equipoSerieCtrl.text.trim(),
                      ));
                    }

                    await _actaRepo.guardarActaCompleta(acta, modulos: modulos, equipos: equipos);

                    setState(() {
                      _pendientesCount++;
                    });

                    Navigator.pop(dialogCtx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        backgroundColor: const Color(0xFF15803D),
                        content: Text('Acta ($offlineId) guardada exitosamente en disco local para ${ipress.nombre}'),
                        duration: const Duration(seconds: 4),
                      ),
                    );
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ActaDetalleScreen(offlineId: offlineId, establecimientoNombre: ipress.nombre),
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: const Icon(Icons.save_rounded, size: 18),
                  label: const Text('Guardar Acta (Offline)'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isDesktop = size.width > 900;

    return Scaffold(
      body: Stack(
        children: [
          Row(
            children: [
              // BARRA LATERAL (SIDEBAR SLATE 900 IDÉNTICO AL SISTEMA LARAVEL)
              if (isDesktop)
                Container(
                  width: 178,
                  color: const Color(0xFF0F172A), // Slate-900 exacto
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Línea cian de degradado superior (2px)
                      Container(height: 2, color: const Color(0xFF06B6D4)),
                      // Header Logo AUTOPSIA TI
                      Container(
                        height: 72,
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        decoration: const BoxDecoration(
                          border: Border(bottom: BorderSide(color: Color(0x12FFFFFF))),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                color: const Color(0xFF06B6D4).withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFF06B6D4).withValues(alpha: 0.3)),
                              ),
                              child: const Icon(Icons.add_rounded, color: Color(0xFF22D3EE), size: 22),
                            ),
                            const SizedBox(width: 8),
                            const Expanded(
                              child: Text(
                                'AUTOPSIA TI',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 13,
                                  letterSpacing: 0.5,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // NAVEGACIÓN Y SECCIONES DEL MENÚ
                      Expanded(
                        child: ListView(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 16),
                          children: [
                            // MENÚ PRINCIPAL
                            _menuItem(Icons.dashboard_rounded, 'Dashboard'),

                            const SizedBox(height: 12),
                            const Padding(
                              padding: EdgeInsets.only(left: 12, bottom: 6, top: 2),
                              child: Text(
                                'PLATAFORMA',
                                style: TextStyle(color: Color(0xFF475569), fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 1.0),
                              ),
                            ),
                            _menuItem(Icons.people_alt_outlined, 'Gestionar Usuarios'),
                            _menuItem(Icons.account_circle_outlined, 'Mi Perfil'),

                            const SizedBox(height: 12),
                            const Padding(
                              padding: EdgeInsets.only(left: 12, bottom: 6, top: 2),
                              child: Text(
                                'OPERACIONES',
                                style: TextStyle(color: Color(0xFF475569), fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 1.0),
                              ),
                            ),
                            _menuItem(Icons.groups_outlined, 'Actas de Reunión'),
                            _menuItem(Icons.local_hospital_outlined, 'Actas de Diagnóstico Situacional'),
                            _menuItem(Icons.business_outlined, 'Establecimientos'),
                            _menuItem(Icons.bar_chart_outlined, 'Reportes', withChevron: true),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

              // CONTENIDO PRINCIPAL SCROLLABLE
              Expanded(
                child: Column(
                  children: [
                    // HEADER SUPERIOR IDÉNTICO AL SISTEMA WEB
                    Container(
                      height: 72,
                      padding: const EdgeInsets.symmetric(horizontal: 28),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  _getPageTitle(),
                                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                  overflow: TextOverflow.ellipsis,
                                ),
                                Text(
                                  _getBreadcrumb(),
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),

                          // BADGE SUPERIOR DE USUARIO AUTENTICADO (IDÉNTICO A LARAVEL) CON LOGOUT INTEGRADO
                          PopupMenuButton<String>(
                            tooltip: 'Menú de usuario',
                            onSelected: (val) {
                              if (val == 'logout') {
                                _cerrarSesion();
                              }
                            },
                            offset: const Offset(0, 48),
                            child: Row(
                              children: [
                                Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    Text(
                                      _getUserDisplayName(), // JORDAN ROBERTO
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                    ),
                                    const Text(
                                      'Administrador',
                                      style: TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.w600),
                                    ),
                                  ],
                                ),
                                const SizedBox(width: 10),
                                const CircleAvatar(
                                  radius: 16,
                                  backgroundColor: Color(0xFF10B981),
                                  child: Text('J', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                                ),
                                const SizedBox(width: 4),
                                const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFF94A3B8), size: 18),
                              ],
                            ),
                            itemBuilder: (context) => [
                              const PopupMenuItem<String>(
                                value: 'logout',
                                child: Row(
                                  children: [
                                    Icon(Icons.logout_rounded, color: Colors.red, size: 18),
                                    SizedBox(width: 8),
                                    Text('Cerrar Sesión'),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // BODY PRINCIPAL
                    Expanded(
                      child: Container(
                        color: Colors.white,
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(28),
                          child: _buildBodyContent(),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          // BADGE FLOTANTE DE ESTADO (PWA ONLINE / OFFLINE) EN LA ESQUINA INFERIOR DERECHA
          Positioned(
            right: 24,
            bottom: 24,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [
                  BoxShadow(color: Color(0x30000000), blurRadius: 12, offset: Offset(0, 4)),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: _isOnline ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _isOnline ? 'PWA Online' : 'Modo Campo Offline',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                  const SizedBox(width: 4),
                  const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white70, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _menuItem(IconData icon, String title, {bool withChevron = false}) {
    return SidebarMenuItem(
      icon: icon,
      title: title,
      active: _selectedMenu == title,
      withChevron: withChevron,
      onTap: () => setState(() => _selectedMenu = title),
    );
  }

  Widget _buildBodyContent() {
    switch (_selectedMenu) {
      case 'Dashboard':
        return DashboardTab(
          totalIpress: _totalIpress,
          sinDiagnostico: _sinDiagnostico,
          conDiagnostico: _conDiagnostico,
          markers: _markers,
        );
      case 'Gestionar Usuarios':
        return GestionarUsuariosTab(realUsers: _realUsers);
      case 'Actas de Reunión':
        return ReunionesTab(reuniones: _reuniones);
      case 'Mi Perfil':
        return PerfilTab(
          userName: _userName,
          isSyncing: _isSyncing,
          onSync: _autoSync,
          onLogout: _cerrarSesion,
        );
      case 'Establecimientos':
        return const EstablecimientosTab();
      case 'Reportes':
        return const ReportesTab();
      case 'Actas de Diagnóstico Situacional':
      default:
        return ActasDiagnosticoTab(
          totalIpress: _totalIpress,
          firmadas: 0,
          pendientesCount: _pendientesCount,
          anuladas: 0,
          searchResult: _searchResult,
          searchCtrl: _searchCtrl,
          onSearch: _onSearch,
          onNuevaActa: _mostrarDialogoNuevaActa,
        );
    }
  }

  String _getPageTitle() {
    switch (_selectedMenu) {
      case 'Dashboard': return 'Mapa de Diagnóstico Situacional';
      case 'Gestionar Usuarios': return 'Gestionar usuarios';
      case 'Mi Perfil': return 'Mi Perfil';
      case 'Actas de Reunión': return 'Actas de Reunión';
      case 'Actas de Diagnóstico Situacional': return 'Actas de Diagnóstico Situacional';
      case 'Establecimientos': return 'Establecimientos';
      case 'Reportes': return 'Reportes';
      default: return _selectedMenu;
    }
  }

  String _getBreadcrumb() {
    switch (_selectedMenu) {
      case 'Dashboard': return 'Plataforma • Mapa General de Diagnóstico Situacional IPRESS';
      case 'Gestionar Usuarios': return 'Administracion • Gestionar Usuarios';
      case 'Mi Perfil': return 'Plataforma • Mi Perfil';
      case 'Actas de Reunión': return 'Operaciones • Actas de Reunión';
      case 'Actas de Diagnóstico Situacional': return 'Operaciones • Actas de Diagnóstico Situacional';
      case 'Establecimientos': return 'Operaciones • Establecimientos';
      case 'Reportes': return 'Operaciones • Reportes';
      default: return 'Panel Principal';
    }
  }

  String _getUserDisplayName() {
    final parts = _userName.toUpperCase().split(' ');
    if (parts.length >= 2) {
      return '${parts[0]} ${parts[1]}';
    }
    return _userName.toUpperCase();
  }
}

/// Fila SI/NO + cantidad/operativos para pozo a tierra o panel solar —
/// espejo de los campos homónimos de mon_cabecera_monitoreo (ver
/// resources/views/usuario/monitoreo/create.blade.php).
class _CampoInstalacion extends StatelessWidget {
  final String label;
  final String valor;
  final TextEditingController cantidadCtrl;
  final TextEditingController operativosCtrl;
  final ValueChanged<String> onChanged;

  const _CampoInstalacion({
    required this.label,
    required this.valor,
    required this.cantidadCtrl,
    required this.operativosCtrl,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(child: Text(label, style: const TextStyle(fontSize: 13, color: Color(0xFF334155)))),
            ChoiceChip(label: const Text('SI'), selected: valor == 'SI', onSelected: (_) => onChanged('SI')),
            const SizedBox(width: 6),
            ChoiceChip(label: const Text('NO'), selected: valor == 'NO', onSelected: (_) => onChanged('NO')),
          ],
        ),
        if (valor == 'SI') ...[
          const SizedBox(height: 6),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: cantidadCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'Cantidad',
                    isDense: true,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: TextField(
                  controller: operativosCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'Operativos',
                    isDense: true,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }
}
