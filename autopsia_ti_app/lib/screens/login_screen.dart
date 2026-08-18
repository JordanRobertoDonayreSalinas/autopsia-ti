import 'package:flutter/material.dart';
import 'package:lottie/lottie.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/sync_service.dart';
import '../widgets/login_illustration_painter.dart';
import 'main_campo_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _userCtrl = TextEditingController(text: '71883058');
  final _passCtrl = TextEditingController(text: '12345678');
  bool _obscurePass = true;
  bool _isLoading = false;
  bool _isOnline = false;

  @override
  void initState() {
    super.initState();
    _checkNetworkStatus();
  }

  Future<void> _checkNetworkStatus() async {
    final syncServ = SyncService();
    final res = await syncServ.checkVersion();
    setState(() {
      _isOnline = res['success'] == true;
    });
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    final username = _userCtrl.text.trim();
    final syncServ = SyncService();
    final res = await syncServ.login(username, _passCtrl.text);
    final prefs = await SharedPreferences.getInstance();

    if (res['success'] == true) {
      final user = res['user'] as Map<String, dynamic>? ?? {};
      final nombreUsuario = user['nombre_completo'] ?? user['name'] ?? username;

      await prefs.setBool('is_logged_in', true);
      await prefs.setString('user_name', nombreUsuario);
      await prefs.setString('user_email', username);
      if (res['token'] != null) await prefs.setString('auth_token', res['token']);
      // Username verificado con éxito online: habilita el reingreso en modo
      // campo si más adelante no hay señal (ver rama offline abajo).
      await prefs.setString('verified_username', username);

      if (!mounted) return;
      setState(() => _isLoading = false);
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const MainCampoScreen()),
      );
      return;
    }

    // Sin conexión con el servidor: ya NO se acepta cualquier usuario/clave.
    // Solo se permite reingresar si este mismo usuario ya validó su
    // contraseña con éxito antes, con internet, en este dispositivo.
    if (res['offline'] == true) {
      final verifiedUsername = prefs.getString('verified_username');
      if (verifiedUsername != null && verifiedUsername == username) {
        await prefs.setBool('is_logged_in', true);

        if (!mounted) return;
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            backgroundColor: Color(0xFFB45309),
            content: Text('Sin conexión: ingresando con la sesión en caché de este dispositivo (modo campo).'),
          ),
        );
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const MainCampoScreen()),
        );
        return;
      }

      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            backgroundColor: Color(0xFFEF4444),
            content: Text('Sin conexión y este usuario no tiene una sesión verificada previamente en este dispositivo.'),
          ),
        );
      }
      return;
    }

    if (mounted) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFFEF4444),
          content: Text(res['message'] ?? 'Credenciales incorrectas'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isDesktop = size.width > 768;

    return Scaffold(
      backgroundColor: const Color(0xFFDCE8FA), // Fondo azul hielo exacto al sistema web
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Container(
            width: isDesktop ? 820 : 420,
            height: isDesktop ? 480 : null,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x1A000000),
                  blurRadius: 40,
                  offset: Offset(0, 10),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: Flex(
                direction: isDesktop ? Axis.horizontal : Axis.vertical,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // LADO IZQUIERDO: ILUSTRACIÓN EXACTA AL SISTEMA WEBLARAVEL
                  Flexible(
                    flex: isDesktop ? 1 : 0,
                    child: Container(
                      width: isDesktop ? 410 : double.infinity,
                      height: isDesktop ? 480 : 230,
                      color: const Color(0xFFF6F9FE),
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          // Círculo de fondo #EBF3FE
                          Container(
                            width: isDesktop ? 290 : 180,
                            height: isDesktop ? 290 : 180,
                            decoration: const BoxDecoration(
                              color: Color(0xFFEBF3FE),
                              shape: BoxShape.circle,
                            ),
                          ),
                          // Animación Lottie oficial de login.json
                          SizedBox(
                            width: isDesktop ? 320 : 220,
                            height: isDesktop ? 260 : 180,
                            child: Lottie.asset(
                              'assets/login.json',
                              fit: BoxFit.contain,
                              errorBuilder: (ctx, err, stack) => CustomPaint(
                                painter: LoginIllustrationPainter(),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // LADO DERECHO: FORMULARIO EXACTO AL SISTEMA WEB LARAVEL
                  Flexible(
                    flex: 1,
                    child: Container(
                      width: isDesktop ? 410 : double.infinity,
                      padding: EdgeInsets.symmetric(
                        horizontal: isDesktop ? 44 : 24,
                        vertical: isDesktop ? 40 : 28,
                      ),
                      color: Colors.white,
                      child: Form(
                        key: _formKey,
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text(
                              'Bienvenido',
                              style: TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF1E3A8A), // Azul profundo exacto al web
                                letterSpacing: -0.5,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Ingresa tus credenciales para acceder al sistema',
                              style: TextStyle(
                                fontSize: 12,
                                color: Color(0xFF64748B),
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 12),

                            // Badge disimulado de estado de red
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: _isOnline ? const Color(0xFFDCFCE7) : const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(16),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _isOnline ? Icons.wifi : Icons.wifi_off,
                                    color: _isOnline ? const Color(0xFF15803D) : const Color(0xFFB45309),
                                    size: 12,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    _isOnline ? 'Online' : 'Modo Campo Offline',
                                    style: TextStyle(
                                      color: _isOnline ? const Color(0xFF15803D) : const Color(0xFFB45309),
                                      fontWeight: FontWeight.bold,
                                      fontSize: 10,
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(height: 24),

                            // CAMPO USUARIO (Fondo #EEF3FE, sin borde, bordes redondeados exactos)
                            Container(
                              margin: const EdgeInsets.only(bottom: 16),
                              child: TextFormField(
                                controller: _userCtrl,
                                maxLength: 8,
                                validator: (v) => (v == null || v.isEmpty) ? 'Ingrese su usuario' : null,
                                style: const TextStyle(fontSize: 14, color: Color(0xFF1E293B), fontWeight: FontWeight.w500),
                                decoration: InputDecoration(
                                  hintText: 'Usuario',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                                  counterText: '',
                                  prefixIcon: const Padding(
                                    padding: EdgeInsets.only(left: 16, right: 12),
                                    child: Icon(Icons.person_outline_rounded, color: Color(0xFF94A3B8), size: 18),
                                  ),
                                  prefixIconConstraints: const BoxConstraints(minWidth: 46),
                                  filled: true,
                                  fillColor: const Color(0xFFEEF3FE), // Fondo celeste claro exacto a la foto
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide.none,
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide.none,
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                                ),
                              ),
                            ),

                            // CAMPO CONTRASEÑA (Fondo #EEF3FE, sin borde)
                            Container(
                              margin: const EdgeInsets.only(bottom: 24),
                              child: TextFormField(
                                controller: _passCtrl,
                                obscureText: _obscurePass,
                                validator: (v) => (v == null || v.isEmpty) ? 'Ingrese su contraseña' : null,
                                style: const TextStyle(fontSize: 14, color: Color(0xFF1E293B), fontWeight: FontWeight.w500),
                                decoration: InputDecoration(
                                  hintText: 'Contraseña',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                                  prefixIcon: const Padding(
                                    padding: EdgeInsets.only(left: 16, right: 12),
                                    child: Icon(Icons.lock_outline_rounded, color: Color(0xFF94A3B8), size: 18),
                                  ),
                                  prefixIconConstraints: const BoxConstraints(minWidth: 46),
                                  suffixIcon: IconButton(
                                    icon: Icon(_obscurePass ? Icons.visibility_off_outlined : Icons.visibility_outlined, color: const Color(0xFF94A3B8), size: 18),
                                    onPressed: () => setState(() => _obscurePass = !_obscurePass),
                                  ),
                                  filled: true,
                                  fillColor: const Color(0xFFEEF3FE), // Fondo celeste claro exacto
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide.none,
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide.none,
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                                ),
                              ),
                            ),

                            // BOTÓN INGRESAR (Azul #2563EB idéntico a la captura)
                            Container(
                              width: double.infinity,
                              height: 48,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: const [
                                  BoxShadow(
                                    color: Color(0x332563EB),
                                    blurRadius: 14,
                                    offset: Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: ElevatedButton(
                                onPressed: _isLoading ? null : _handleLogin,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF2563EB), // Azul vibrante exacto
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                child: _isLoading
                                    ? Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: const [
                                          SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
                                          SizedBox(width: 8),
                                          Text('Verificando...', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                        ],
                                      )
                                    : const Text(
                                        'Ingresar',
                                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                      ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
