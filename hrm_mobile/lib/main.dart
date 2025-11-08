import 'package:flutter/material.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'register_page.dart';
import 'login_page.dart';
import 'home_page.dart';
import 'leave_history_page.dart';

final ThemeData hrmTheme = ThemeData(
  primaryColor: const Color(0xFF0d6efd),
  colorScheme: ColorScheme.fromSeed(
    seedColor: const Color(0xFF0d6efd),
    primary: const Color(0xFF0d6efd),
    secondary: const Color(0xFF6c757d),
    background: const Color(0xFFf8f9fa),
    surface: Colors.white,
    onPrimary: Colors.white,
    onSecondary: Colors.white,
    onBackground: Colors.black,
    onSurface: Colors.black,
  ),
  scaffoldBackgroundColor: const Color(0xFFf8f9fa),
  appBarTheme: const AppBarTheme(
    backgroundColor: Color(0xFF0d6efd),
    foregroundColor: Colors.white,
    elevation: 0,
    centerTitle: true,
    titleTextStyle: TextStyle(
      fontWeight: FontWeight.bold,
      fontSize: 20,
      color: Colors.white,
    ),
  ),
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: const Color(0xFF0d6efd),
      foregroundColor: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      textStyle: const TextStyle(fontWeight: FontWeight.bold),
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 24),
      elevation: 2,
    ),
  ),
  cardTheme: CardThemeData(
    color: Colors.white,
    elevation: 2,
    margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 0),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(16),
    ),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: Colors.white,
    border: OutlineInputBorder(
      borderRadius: const BorderRadius.all(Radius.circular(12)),
      borderSide: BorderSide(color: Colors.grey.shade300),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: const BorderRadius.all(Radius.circular(12)),
      borderSide: BorderSide(color: Colors.grey.shade300),
    ),
    focusedBorder: const OutlineInputBorder(
      borderRadius: BorderRadius.all(Radius.circular(12)),
      borderSide: BorderSide(color: Color(0xFF0d6efd), width: 2),
    ),
    labelStyle: const TextStyle(color: Color(0xFF495057)),
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
  ),
  textTheme: const TextTheme(
    bodyLarge: TextStyle(fontSize: 16, color: Color(0xFF212529)),
    bodyMedium: TextStyle(fontSize: 14, color: Color(0xFF212529)),
    titleLarge: TextStyle(fontWeight: FontWeight.bold, fontSize: 22, color: Color(0xFF212529)),
    titleMedium: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF212529)),
  ),
  bottomNavigationBarTheme: const BottomNavigationBarThemeData(
    backgroundColor: Colors.white,
    selectedItemColor: Color(0xFF0d6efd),
    unselectedItemColor: Color(0xFF6c757d),
    showUnselectedLabels: true,
    type: BottomNavigationBarType.fixed,
    elevation: 8,
    selectedLabelStyle: TextStyle(fontWeight: FontWeight.bold),
  ),
);

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('fr_FR', null);
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'HRM Mobile',
      theme: hrmTheme,
      debugShowCheckedModeBanner: false,
      initialRoute: '/login',
      routes: {
        '/login': (context) => const LoginPage(),
        '/register': (context) => const RegisterPage(),
        '/home': (context) {
          final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
          final employeId = args?['employe_id'] as int? ?? 0;
          return HomePage(employeId: employeId);
        },
        '/leave_history': (context) {
          final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
          final employeId = args?['employe_id'] as int? ?? 0;
          return LeaveHistoryPage(employeId: employeId);
        },
      },
    );
  }
}
