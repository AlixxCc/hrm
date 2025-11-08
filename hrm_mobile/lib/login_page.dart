import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'home_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  String message = '';
  bool isLoading = false;

  Future<void> login() async {
    setState(() {
      isLoading = true;
      message = '';
    });
    final url = 'http://172.20.10.3/HRM/HRM/api/login.php';
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': emailController.text.trim(),
          'mot_de_passe': passwordController.text.trim(),
        }),
      );
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        setState(() {
          message = 'Connexion réussie !';
        });
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => HomePage(employeId: data['employe_id']),
          ),
        );
      } else {
        setState(() {
          message = data['error'] ?? 'Erreur inconnue';
        });
      }
    } catch (e) {
      setState(() {
        message = 'Erreur réseau : $e';
      });
    }
    setState(() {
      isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Connexion')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            TextField(
              controller: emailController,
              decoration: const InputDecoration(labelText: 'Email'),
            ),
            TextField(
              controller: passwordController,
              decoration: const InputDecoration(labelText: 'Mot de passe'),
              obscureText: true,
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: isLoading ? null : login,
              child: isLoading
                  ? const CircularProgressIndicator()
                  : const Text('Se connecter'),
            ),
            const SizedBox(height: 20),
            Text(message, style: const TextStyle(color: Colors.red)),
            TextButton(
              onPressed: () {
                Navigator.pushReplacementNamed(context, '/register');
              },
              child: const Text('Pas encore de compte ? S\'inscrire'),
            ),
          ],
        ),
      ),
    );
  }
}
