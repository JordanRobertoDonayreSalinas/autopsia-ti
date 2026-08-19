import 'package:flutter/material.dart';

/// CustomPainter para replicar la ilustración exacta (Monitor + Robot Rojo + Servidor + Carpeta)
/// Se usa como respaldo si el asset Lottie 'assets/login.json' no carga.
class LoginIllustrationPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // 1. Servidor en el lado izquierdo (Server Rack)
    final serverPaint = Paint()..color = const Color(0xFF334155);
    final serverRect = RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.05, h * 0.4, w * 0.16, h * 0.5), const Radius.circular(4));
    canvas.drawRRect(serverRect, serverPaint);

    final slotPaint = Paint()..color = const Color(0xFF1E293B);
    final lightPaint = Paint()..color = const Color(0xFF38BDF8);
    for (int i = 0; i < 4; i++) {
      canvas.drawRRect(RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.07, h * 0.44 + (i * 22), w * 0.12, 14), const Radius.circular(2)), slotPaint);
      canvas.drawCircle(Offset(w * 0.09, h * 0.44 + (i * 22) + 7), 2, lightPaint);
    }

    // 2. Carpeta Roja en la izquierda superior
    final folderPaint = Paint()..color = const Color(0xFFEF4444);
    final folderPath = Path()
      ..moveTo(w * 0.06, h * 0.3)
      ..lineTo(w * 0.12, h * 0.3)
      ..lineTo(w * 0.15, h * 0.34)
      ..lineTo(w * 0.22, h * 0.34)
      ..lineTo(w * 0.22, h * 0.42)
      ..lineTo(w * 0.06, h * 0.42)
      ..close();
    canvas.drawPath(folderPath, folderPaint);

    // 3. Monitor de Computadora Central
    final standPaint = Paint()..color = const Color(0xFF94A3B8);
    canvas.drawRect(Rect.fromLTWH(w * 0.45, h * 0.72, w * 0.08, h * 0.15), standPaint);
    canvas.drawRRect(RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.38, h * 0.85, w * 0.22, 8), const Radius.circular(4)), standPaint);

    final monitorFramePaint = Paint()..color = const Color(0xFF1E293B);
    final monitorRect = RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.25, h * 0.15, w * 0.48, h * 0.58), const Radius.circular(8));
    canvas.drawRRect(monitorRect, monitorFramePaint);

    final screenPaint = Paint()..color = Colors.white;
    final screenRect = RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.27, h * 0.18, w * 0.44, h * 0.52), const Radius.circular(4));
    canvas.drawRRect(screenRect, screenPaint);

    // Gráfico de torta (Pie chart) en el monitor
    final pieRed = Paint()..color = const Color(0xFFEF4444);
    final pieBlue = Paint()..color = const Color(0xFF3B82F6);
    final pieCenter = Offset(w * 0.48, h * 0.42);
    canvas.drawArc(Rect.fromCircle(center: pieCenter, radius: 24), -1.5, 4.0, true, pieBlue);
    canvas.drawArc(Rect.fromCircle(center: pieCenter, radius: 24), 2.5, 2.0, true, pieRed);

    // Gráfico de líneas en la pantalla
    final linePaint = Paint()
      ..color = const Color(0xFF3B82F6)
      ..strokeWidth = 2
      ..style = PaintingStyle.stroke;
    final linePath = Path()
      ..moveTo(w * 0.3, h * 0.55)
      ..lineTo(w * 0.34, h * 0.48)
      ..lineTo(w * 0.38, h * 0.52)
      ..lineTo(w * 0.42, h * 0.38);
    canvas.drawPath(linePath, linePaint);

    // 4. Mascotita Robot Rojo en el lado derecho de la pantalla
    final robotPaint = Paint()..color = const Color(0xFFEF4444);
    // Cabeza
    canvas.drawRRect(RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.72, h * 0.48, 28, 22), const Radius.circular(6)), robotPaint);
    // Ojos del robot
    canvas.drawCircle(Offset(w * 0.72 + 8, h * 0.48 + 10), 3, Paint()..color = Colors.white);
    canvas.drawCircle(Offset(w * 0.72 + 20, h * 0.48 + 10), 3, Paint()..color = Colors.white);
    // Antena robot
    canvas.drawRect(Rect.fromLTWH(w * 0.72 + 12, h * 0.48 - 6, 4, 6), robotPaint);
    canvas.drawCircle(Offset(w * 0.72 + 14, h * 0.48 - 8), 3, Paint()..color = const Color(0xFFFBBF24));
    // Cuerpo robot
    canvas.drawRRect(RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.70, h * 0.72, 32, 28), const Radius.circular(8)), robotPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
