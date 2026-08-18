// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter_test/flutter_test.dart';

import 'package:autopsia_ti_app/main.dart';

void main() {
  testWidgets('Autopsia TI App smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const AutopsiaTiApp(initialLoggedIn: true));
    await tester.pump();

    // Verify that our title is displayed.
    expect(find.text('Mapa de Diagnóstico Situacional'), findsOneWidget);
  });
}
