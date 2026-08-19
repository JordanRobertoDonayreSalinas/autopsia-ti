import 'dart:async';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/establecimiento.dart';
import '../models/reunion.dart';
import '../repositories/acta_repository.dart';
import '../repositories/establecimiento_repository.dart';
import '../repositories/reunion_repository.dart';
import '../services/sync_service.dart';
import '../widgets/sidebar_menu_item.dart';
import 'login_screen.dart';
import 'nueva_acta_form_screen.dart';
import 'reunion_form_screen.dart';
import 'tabs/actas_diagnostico_tab.dart';
import 'tabs/actas_monitoreo_listado_tab.dart';
import 'tabs/dashboard_tab.dart';
import 'tabs/establecimientos_tab.dart';
import 'tabs/gestionar_usuarios_tab.dart';
import 'tabs/perfil_tab.dart';
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
  List<String> _aniosDisponibles = [];
  String _anioSeleccionado = 'todos';
  List<Map<String, dynamic>> _realUsers = [];
  List<Reunion> _reuniones = [];
  int _pendientesCount = 0;
  String _selectedMenu = 'Dashboard';
  String _userName = 'JORDAN ROBERTO';
  List<Establecimiento> _searchResult = [];
  final TextEditingController _searchCtrl = TextEditingController();
  Timer? _reconnectTimer;

  @override
  void initState() {
    super.initState();
    _checkStatus();
    _loadInitialData();
    // Reintenta la conexión sola cada 20s mientras esté offline — antes,
    // si el chequeo inicial fallaba (ej. el servidor tardó en levantar),
    // la app se quedaba "Modo Campo Offline" para siempre sin reintentar.
    _reconnectTimer = Timer.periodic(const Duration(seconds: 20), (_) {
      if (!_isOnline) _checkStatus();
    });
  }

  @override
  void dispose() {
    _reconnectTimer?.cancel();
    super.dispose();
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
      final stats = await _syncService.getDashboardStats(anio: _anioSeleccionado);
      final mapa = await _syncService.getMapMarkers(anio: _anioSeleccionado);
      final users = await _syncService.getUsers();

      setState(() {
        if (stats['success'] == true) {
          _totalIpress = stats['total_ipress'] ?? 0;
          _sinDiagnostico = stats['sin_diagnostico'] ?? 0;
          _conDiagnostico = stats['con_diagnostico'] ?? 0;
        }
        _markers = mapa['markers'] ?? [];
        _aniosDisponibles = mapa['anios_disponibles'] ?? [];
        _realUsers = users;
      });
    } catch (_) {}
  }

  /// El filtro "Año" recalcula en el servidor qué establecimientos cuentan
  /// como "con diagnóstico" (espejo de UsuarioController::index, que hace
  /// una recarga completa de página con ?anio=X) — a diferencia de los demás
  /// filtros del dashboard, que son puramente del lado del cliente.
  Future<void> _onAnioChanged(String anio) async {
    setState(() => _anioSeleccionado = anio);
    await _fetchApiData();
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
    final resultadoReuniones = await _reunionRepo.sincronizarPendientes();
    await _loadInitialData();
    setState(() => _isSyncing = false);

    if (!mounted) return;
    _refrescarListadoActas();
    final sincronizados = ((resultado['sincronizados'] ?? 0) as int) + ((resultadoReuniones['sincronizados'] ?? 0) as int);
    final errores = [
      ...List<Map<String, dynamic>>.from(resultado['errores'] ?? []),
      ...List<Map<String, dynamic>>.from(resultadoReuniones['errores'] ?? []),
    ];
    if (errores.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFFB91C1C),
          content: Text(
            sincronizados > 0
                ? '$sincronizados registro(s) sincronizado(s). ${errores.length} con error: ${errores.first['message']}'
                : 'No se pudo sincronizar: ${errores.first['message']}',
          ),
          duration: const Duration(seconds: 6),
        ),
      );
    } else if (sincronizados > 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFF15803D),
          content: Text('$sincronizados registro(s) sincronizado(s) con el servidor.'),
        ),
      );
    }
  }

  Future<void> _abrirNuevaReunion() async {
    final guardado = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const ReunionFormScreen()),
    );
    if (guardado == true) {
      final reuniones = await _reunionRepo.obtenerTodas();
      if (!mounted) return;
      setState(() => _reuniones = reuniones);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFF15803D), content: Text('Acta de reunión guardada en disco local.')),
      );
    }
  }

  Future<void> _recargarUsuario() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() => _userName = prefs.getString('user_name') ?? _userName);
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

  /// "Nueva Acta" es el formulario completo real (fecha, implementador,
  /// categoría, responsable, equipo mínimo 1, pozo/panel, fotos) — ver
  /// NuevaActaFormScreen. Antes esto era un diálogo simplificado que ni
  /// siquiera pedía el campo `equipo`, obligatorio en el store() real.
  void _mostrarDialogoNuevaActa(Establecimiento? itemSeleccionado) {
    final ipress = itemSeleccionado ?? (_searchResult.isNotEmpty ? _searchResult.first : null);
    if (ipress == null || ipress.id == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(backgroundColor: Color(0xFFB91C1C), content: Text('Seleccione un establecimiento primero.')),
      );
      return;
    }
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => NuevaActaFormScreen(establecimiento: ipress, userName: _userName, usuariosDisponibles: _realUsers),
      ),
    ).then((_) {
      // Recalcula desde SQLite en vez de asumir que se guardó algo — el
      // usuario pudo haber cancelado/vuelto atrás sin guardar.
      _loadInitialData();
      _refrescarListadoActas();
    });
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
          // Tocable: reintenta la verificación de conexión al toque, en vez
          // de quedar fijo en "Modo Campo Offline" hasta reiniciar la app.
          Positioned(
            right: 24,
            bottom: 24,
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                borderRadius: BorderRadius.circular(20),
                onTap: () async {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Verificando conexión...'), duration: Duration(seconds: 1)),
                  );
                  await _checkStatus();
                },
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
                      const Icon(Icons.refresh_rounded, color: Colors.white70, size: 16),
                    ],
                  ),
                ),
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
          aniosDisponibles: _aniosDisponibles,
          anioSeleccionado: _anioSeleccionado,
          onAnioChanged: _onAnioChanged,
        );
      case 'Gestionar Usuarios':
        return GestionarUsuariosTab(
          realUsers: _realUsers,
          syncService: _syncService,
          onChanged: _fetchApiData,
        );
      case 'Actas de Reunión':
        return ReunionesTab(reuniones: _reuniones, onNuevaReunion: _abrirNuevaReunion);
      case 'Mi Perfil':
        return PerfilTab(
          userName: _userName,
          isSyncing: _isSyncing,
          onSync: _autoSync,
          onLogout: _cerrarSesion,
          onProfileUpdated: _recargarUsuario,
        );
      case 'Establecimientos':
        return const EstablecimientosTab();
      case 'Actas de Diagnóstico Situacional':
      default:
        return ActasMonitoreoListadoTab(
          key: ValueKey(_actasListadoRefreshKey),
          onNuevaActa: _abrirNuevaActaScreen,
          onSincronizar: _autoSync,
          isSyncing: _isSyncing,
        );
    }
  }

  /// Fuerza que ActasMonitoreoListadoTab se reconstruya (y por lo tanto
  /// vuelva a pedir el listado real al servidor) al volver de "Nueva Acta"
  /// o de un autoSync — el widget no expone otra forma de refrescar porque
  /// administra su propio estado (filtros, paginación) internamente.
  int _actasListadoRefreshKey = 0;
  void _refrescarListadoActas() => setState(() => _actasListadoRefreshKey++);

  /// "Nueva Acta" es una pantalla aparte en Laravel (`/crear-acta`), no el
  /// listado — acá reutiliza el mismo buscador de establecimientos y
  /// diálogo de creación que ya existían, solo que ahora vive en su propia
  /// pantalla en vez de ser la vista principal de "Actas de Diagnóstico".
  Future<void> _abrirNuevaActaScreen() async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => Scaffold(
          backgroundColor: const Color(0xFFF1F5F9),
          appBar: AppBar(
            title: const Text('Nueva Acta'),
            backgroundColor: const Color(0xFF0F172A),
            foregroundColor: Colors.white,
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ActasDiagnosticoTab(
              totalIpress: _totalIpress,
              firmadas: 0,
              pendientesCount: _pendientesCount,
              anuladas: 0,
              searchResult: _searchResult,
              searchCtrl: _searchCtrl,
              onSearch: _onSearch,
              onNuevaActa: _mostrarDialogoNuevaActa,
            ),
          ),
        ),
      ),
    );
    _refrescarListadoActas();
  }

  String _getPageTitle() {
    switch (_selectedMenu) {
      case 'Dashboard': return 'Mapa de Diagnóstico Situacional';
      case 'Gestionar Usuarios': return 'Gestionar usuarios';
      case 'Mi Perfil': return 'Mi Perfil';
      case 'Actas de Reunión': return 'Actas de Reunión';
      case 'Actas de Diagnóstico Situacional': return 'Actas de Diagnóstico Situacional';
      case 'Establecimientos': return 'Establecimientos';
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
