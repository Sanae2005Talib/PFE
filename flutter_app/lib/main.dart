import 'package:flutter/material.dart';
import 'screens/home_screen.dart';
import 'screens/associations_list.dart';
import 'screens/login_screen_improved.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Solidarité Connect',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        // Couleurs principales
        primaryColor: const Color(0xFF10B981), // Vert
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF10B981),
          secondary: const Color(0xFF0EA5E9), // Bleu
        ),
        useMaterial3: true,
        
        // AppBar style
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          elevation: 0,
          backgroundColor: Color(0xFF10B981),
          foregroundColor: Colors.white,
        ),
        
        // Card style
        cardTheme: CardThemeData(
          elevation: 2,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        
        // Button style
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF10B981),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
        ),
      ),
      
      // Page de démarrage - Login Admin (Page lwla)
      home: const LoginScreenImproved(),
      
      // Routes nommées pour navigation
      routes: {
        '/home': (context) => const HomeScreen(),
        '/associations': (context) => const AssociationsListScreen(),
        '/login': (context) => const LoginScreenImproved(),
      },
    );
  }
}
