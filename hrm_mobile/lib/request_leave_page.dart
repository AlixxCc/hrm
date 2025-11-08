import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class RequestLeavePage extends StatefulWidget {
  final int employeId;
  const RequestLeavePage({super.key, required this.employeId});

  @override
  State<RequestLeavePage> createState() => _RequestLeavePageState();
}

class _RequestLeavePageState extends State<RequestLeavePage> {
  final _formKey = GlobalKey<FormState>();
  final _zoneController = TextEditingController();
  final _motifController = TextEditingController();

  DateTime? _dateDebut;
  DateTime? _dateFin;
  String _nature = 'Congé annuel';
  String message = '';
  bool isLoading = false;

  final List<String> _natureOptions = [
    'Congé annuel',
    'Congé maladie',
    'Congé sans solde',
    'Congé maternité/paternité',
    'Congé formation',
    'Autre',
  ];

  @override
  void dispose() {
    _zoneController.dispose();
    _motifController.dispose();
    super.dispose();
  }

  Future<void> _selectDate(BuildContext context, bool isStartDate) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    
    if (picked != null) {
      setState(() {
        if (isStartDate) {
          _dateDebut = picked;
          if (_dateFin != null && _dateFin!.isBefore(_dateDebut!)) {
            _dateFin = null;
          }
        } else {
          _dateFin = picked;
        }
      });
    }
  }

  String _formatDate(DateTime? date) {
    if (date == null) return 'Sélectionner';
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  String _formatDateForApi(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  Future<void> sendRequest() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_dateDebut == null || _dateFin == null) {
      setState(() {
        message = 'Veuillez sélectionner les dates de début et de fin';
      });
      return;
    }

    setState(() {
      isLoading = true;
      message = '';
    });

    final url = 'http://172.20.10.3/HRM/HRM/api/conges.php';
    
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          "employe_id": widget.employeId,
          "zone_intervention": _zoneController.text.trim(),
          "date_debut": _formatDateForApi(_dateDebut!),
          "date_fin": _formatDateForApi(_dateFin!),
          "nature": _nature,
          "motif": _motifController.text.trim()
        }),
      );

      final data = jsonDecode(response.body);
      
      if (data['success'] == true) {
        setState(() {
          message = 'Demande de congé envoyée avec succès !\nNuméro: ${data['numero_conge']}';
          _zoneController.clear();
          _motifController.clear();
          _dateDebut = null;
          _dateFin = null;
          _nature = 'Congé annuel';
        });
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
      appBar: AppBar(title: const Text('Demander un congé')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue[50],
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.blue[200]!),
                ),
                child: Row(
                  children: [
                    Icon(Icons.info_outline, color: Colors.blue[700], size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Vos informations (nom, fonction, service, etc.) seront complétées automatiquement',
                        style: TextStyle(color: Colors.blue[700], fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              
              TextFormField(
                controller: _zoneController,
                decoration: InputDecoration(
                  labelText: 'Zone d\'intervention',
                  prefixIcon: const Icon(Icons.location_on_outlined),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer la zone d\'intervention';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              
              InkWell(
                onTap: () => _selectDate(context, true),
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'Date de début',
                    prefixIcon: const Icon(Icons.calendar_today),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text(
                    _formatDate(_dateDebut),
                    style: TextStyle(color: _dateDebut == null ? Colors.grey : Colors.black),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              
              InkWell(
                onTap: () => _selectDate(context, false),
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'Date de fin',
                    prefixIcon: const Icon(Icons.calendar_today),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text(
                    _formatDate(_dateFin),
                    style: TextStyle(color: _dateFin == null ? Colors.grey : Colors.black),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              
              DropdownButtonFormField<String>(
                value: _nature,
                decoration: InputDecoration(
                  labelText: 'Nature du congé',
                  prefixIcon: const Icon(Icons.category_outlined),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                items: _natureOptions.map((String value) {
                  return DropdownMenuItem<String>(
                    value: value,
                    child: Text(value),
                  );
                }).toList(),
                onChanged: (String? newValue) {
                  if (newValue != null) {
                    setState(() {
                      _nature = newValue;
                    });
                  }
                },
              ),
              const SizedBox(height: 16),
              
              TextFormField(
                controller: _motifController,
                maxLines: 4,
                decoration: InputDecoration(
                  labelText: 'Motif',
                  alignLabelWithHint: true,
                  prefixIcon: const Padding(
                    padding: EdgeInsets.only(bottom: 60),
                    child: Icon(Icons.description_outlined),
                  ),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer le motif de votre demande';
                  }
                  return null;
                },
              ),
              
              if (message.isNotEmpty) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: message.contains('succès') ? Colors.green[50] : Colors.red[50],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: message.contains('succès') ? Colors.green[200]! : Colors.red[200]!,
                    ),
                  ),
                  child: Text(
                    message,
                    style: TextStyle(
                      color: message.contains('succès') ? Colors.green[700] : Colors.red[700],
                    ),
                  ),
                ),
              ],
              const SizedBox(height: 24),
              
              ElevatedButton(
                onPressed: isLoading ? null : sendRequest,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Envoyer la demande', style: TextStyle(fontSize: 16)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
