import 'package:flutter/material.dart';

/// Catálogo de elementos del editor de croquis — espejo de STYLES/HW_LABEL
/// del editor real (infraestructura_2d.blade.php). Cada entrada define
/// tamaño por defecto y estilo visual para un `type`+`subtype`.
class CroquisTipoInfo {
  final String label;
  final IconData icon;
  final Color fill;
  final Color stroke;
  final Color ink;
  final double defaultW;
  final double defaultH;
  final bool dashed;

  const CroquisTipoInfo({
    required this.label,
    required this.icon,
    required this.fill,
    required this.stroke,
    required this.ink,
    required this.defaultW,
    required this.defaultH,
    this.dashed = false,
  });
}

class CroquisCatalogo {
  static const ambientes = <String, CroquisTipoInfo>{
    'consultorio_fisico': CroquisTipoInfo(label: 'Consultorio Físico', icon: Icons.local_hospital_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF4ADE80), ink: Color(0xFF166534), defaultW: 120, defaultH: 100),
    'consultorio_funcional': CroquisTipoInfo(label: 'Consultorio Funcional', icon: Icons.sync_rounded, fill: Color(0xFFFFFBEB), stroke: Color(0xFFFBBF24), ink: Color(0xFF92400E), defaultW: 120, defaultH: 100, dashed: true),
    'quirofano': CroquisTipoInfo(label: 'Quirófano', icon: Icons.medical_services_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF60A5FA), ink: Color(0xFF1E40AF), defaultW: 120, defaultH: 100),
    'emergencias': CroquisTipoInfo(label: 'Emergencias', icon: Icons.emergency_rounded, fill: Color(0xFFFEF2F2), stroke: Color(0xFFF87171), ink: Color(0xFF991B1B), defaultW: 120, defaultH: 100),
    'administracion': CroquisTipoInfo(label: 'Administración', icon: Icons.folder_rounded, fill: Color(0xFFFAF5FF), stroke: Color(0xFFC084FC), ink: Color(0xFF6B21A8), defaultW: 120, defaultH: 100),
    'baño': CroquisTipoInfo(label: 'Baño', icon: Icons.wc_rounded, fill: Color(0xFFECFEFF), stroke: Color(0xFF22D3EE), ink: Color(0xFF155E75), defaultW: 120, defaultH: 100),
  };

  static const hardware = <String, CroquisTipoInfo>{
    'pc': CroquisTipoInfo(label: 'CPU', icon: Icons.memory_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'laptop': CroquisTipoInfo(label: 'LAPTOP', icon: Icons.laptop_mac_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'monitor': CroquisTipoInfo(label: 'MONITOR', icon: Icons.desktop_windows_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'teclado': CroquisTipoInfo(label: 'TECLADO', icon: Icons.keyboard_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'mouse': CroquisTipoInfo(label: 'MOUSE', icon: Icons.mouse_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'impresora': CroquisTipoInfo(label: 'IMPRESORA', icon: Icons.print_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'ticketera': CroquisTipoInfo(label: 'TICKETERA', icon: Icons.receipt_long_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'escaner': CroquisTipoInfo(label: 'LECTOR', icon: Icons.qr_code_scanner_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'tablet': CroquisTipoInfo(label: 'TABLET', icon: Icons.tablet_mac_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'router': CroquisTipoInfo(label: 'ROUTER', icon: Icons.router_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'ap': CroquisTipoInfo(label: 'ACCESS POINT', icon: Icons.wifi_tethering_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'switch': CroquisTipoInfo(label: 'SWITCH', icon: Icons.lan_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'punto_red': CroquisTipoInfo(label: 'PUNTO RED', icon: Icons.share_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'pozo': CroquisTipoInfo(label: 'POZO TIERRA', icon: Icons.anchor_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'ups': CroquisTipoInfo(label: 'UPS', icon: Icons.battery_charging_full_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'panel_solar': CroquisTipoInfo(label: 'PANEL SOLAR', icon: Icons.wb_sunny_rounded, fill: Color(0xFFF0FDF4), stroke: Color(0xFF22C55E), ink: Color(0xFF166534), defaultW: 62, defaultH: 58),
    'aire_acondicionado': CroquisTipoInfo(label: 'AIRE ACONDICIONADO', icon: Icons.ac_unit_rounded, fill: Color(0xFFEFF6FF), stroke: Color(0xFF3B82F6), ink: Color(0xFF1E3A8A), defaultW: 62, defaultH: 58),
    'toma_estabilizada': CroquisTipoInfo(label: 'TOMA ESTABILIZADA', icon: Icons.bolt_rounded, fill: Color(0xFFFFF7ED), stroke: Color(0xFFFB923C), ink: Color(0xFF9A3412), defaultW: 62, defaultH: 58),
    'toma_comercial': CroquisTipoInfo(label: 'TOMA COMERCIAL', icon: Icons.power_rounded, fill: Color(0xFFF8FAFC), stroke: Color(0xFF94A3B8), ink: Color(0xFF334155), defaultW: 62, defaultH: 58),
  };

  static const puertas = <String, CroquisTipoInfo>{
    'interna': CroquisTipoInfo(label: 'Puerta Interna', icon: Icons.door_front_door_rounded, fill: Color(0xFFF8FAFC), stroke: Color(0xFF64748B), ink: Color(0xFF334155), defaultW: 40, defaultH: 40),
    'externa': CroquisTipoInfo(label: 'Acceso Externo', icon: Icons.fence_rounded, fill: Color(0xFFFEF2F2), stroke: Color(0xFF1F2937), ink: Color(0xFF7F1D1D), defaultW: 180, defaultH: 70),
  };

  static const calles = <String, CroquisTipoInfo>{
    'avenida': CroquisTipoInfo(label: 'Avenida', icon: Icons.add_road_rounded, fill: Color(0xFFF1F5F9), stroke: Color(0xFF94A3B8), ink: Color(0xFF334155), defaultW: 500, defaultH: 80),
    'jiron': CroquisTipoInfo(label: 'Jirón', icon: Icons.add_road_rounded, fill: Color(0xFFF1F5F9), stroke: Color(0xFF94A3B8), ink: Color(0xFF334155), defaultW: 400, defaultH: 60),
    'pasaje': CroquisTipoInfo(label: 'Pasaje', icon: Icons.add_road_rounded, fill: Color(0xFFF1F5F9), stroke: Color(0xFF94A3B8), ink: Color(0xFF334155), defaultW: 300, defaultH: 40),
  };

  static const sistemas = <String, CroquisTipoInfo>{
    'tua': CroquisTipoInfo(label: 'TUA', icon: Icons.dashboard_rounded, fill: Color(0xFFFDF4FF), stroke: Color(0xFFD946EF), ink: Color(0xFF86198F), defaultW: 80, defaultH: 70),
    'sihce': CroquisTipoInfo(label: 'SIHCE', icon: Icons.description_rounded, fill: Color(0xFFFDF4FF), stroke: Color(0xFFD946EF), ink: Color(0xFF86198F), defaultW: 80, defaultH: 70),
    'sismed': CroquisTipoInfo(label: 'SISMED', icon: Icons.medication_rounded, fill: Color(0xFFFDF4FF), stroke: Color(0xFFD946EF), ink: Color(0xFF86198F), defaultW: 80, defaultH: 70),
    'hisminsa': CroquisTipoInfo(label: 'HISMINSA', icon: Icons.monitor_heart_rounded, fill: Color(0xFFFDF4FF), stroke: Color(0xFFD946EF), ink: Color(0xFF86198F), defaultW: 80, defaultH: 70),
    'sisgalenplus': CroquisTipoInfo(label: 'SISGALENPLUS', icon: Icons.add_box_rounded, fill: Color(0xFFFDF4FF), stroke: Color(0xFFD946EF), ink: Color(0xFF86198F), defaultW: 80, defaultH: 70),
  };

  static const Map<String, Map<String, CroquisTipoInfo>> porTipo = {
    'ambiente': ambientes,
    'hardware': hardware,
    'puerta': puertas,
    'calle': calles,
    'sistema': sistemas,
  };

  static const _defaultInfo = CroquisTipoInfo(label: 'ELEMENTO', icon: Icons.crop_square_rounded, fill: Color(0xFFF1F5F9), stroke: Color(0xFF94A3B8), ink: Color(0xFF334155), defaultW: 80, defaultH: 70);

  static CroquisTipoInfo infoDe(String type, String? subtype) {
    return porTipo[type]?[subtype] ?? _defaultInfo;
  }

  static const estadoColores = <String, Color>{
    'REGULAR': Color(0xFFF59E0B),
    'INOPERATIVO': Color(0xFFEF4444),
  };
}
