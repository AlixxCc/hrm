import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'dart:io';

class SendPlanningPage extends StatefulWidget {
  final int employeId;
  const SendPlanningPage({Key? key, required this.employeId}) : super(key: key);

  @override
  State<SendPlanningPage> createState() => _SendPlanningPageState();
}

class _SendPlanningPageState extends State<SendPlanningPage> {
  final _formKey = GlobalKey<FormState>();
  DateTime _selectedWeekStart = DateTime.now();
  File? _selectedFile;
  String _fileName = '';
  bool _isLoading = false;
  String _message = '';

  DateTime _getWeekStart(DateTime date) {
    return date.subtract(Duration(days: date.weekday - 1));
  }

  DateTime _getWeekEnd(DateTime date) {
    return _getWeekStart(date).add(const Duration(days: 6));
  }

  Future<void> _pickFile() async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
      );

      if (result != null) {
        setState(() {
          _selectedFile = File(result.files.single.path!);
          _fileName = result.files.single.name;
        });
      }
    } catch (e) {
      setState(() {
        _message = 'Erreur lors de la sélection du fichier';
      });
    }
  }

  Future<void> _selectWeek(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedWeekStart,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      helpText: 'Sélectionner une date de la semaine',
    );

    if (picked != null) {
      setState(() {
        _selectedWeekStart = _getWeekStart(picked);
      });
    }
  }

  Future<void> _sendPlanning() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedFile == null) {
      setState(() {
        _message = 'Veuillez sélectionner un fichier';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _message = '';
    });

    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('http://172.20.10.3/HRM/HRM/api/plannings.php'),
      );

      final weekEnd = _getWeekEnd(_selectedWeekStart);
      
      request.fields['employe_id'] = widget.employeId.toString();
      request.fields['date_debut'] = DateFormat('yyyy-MM-dd').format(_selectedWeekStart);
      request.fields['date_fin'] = DateFormat('yyyy-MM-dd').format(weekEnd);

      request.files.add(await http.MultipartFile.fromPath(
        'document',
        _selectedFile!.path,
      ));

      var response = await request.send();
      var responseData = await response.stream.bytesToString();

      if (response.statusCode == 200) {
        setState(() {
          _message = 'Planning envoyé avec succès !';
          _selectedFile = null;
          _fileName = '';
        });
      } else {
        setState(() {
          _message = 'Erreur lors de l\'envoi : $responseData';
        });
      }
    } catch (e) {
      setState(() {
        _message = 'Erreur réseau : $e';
      });
    }

    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final weekEnd = _getWeekEnd(_selectedWeekStart);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Envoyer un planning'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.orange.shade50, Colors.orange.shade100],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(Icons.event_note, color: Colors.orange.shade700, size: 32),
                    ),
                    const SizedBox(width: 16),
                    const Expanded(
                      child: Text(
                        'Envoyez votre planning hebdomadaire',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(color: Colors.grey.shade300),
                ),
                child: InkWell(
                  onTap: () => _selectWeek(context),
                  borderRadius: BorderRadius.circular(16),
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.date_range, color: Colors.orange),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Semaine du planning',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${DateFormat('dd/MM/yyyy').format(_selectedWeekStart)} - ${DateFormat('dd/MM/yyyy').format(weekEnd)}',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(Icons.arrow_forward_ios, size: 16),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(color: Colors.grey.shade300),
                ),
                child: InkWell(
                  onTap: _pickFile,
                  borderRadius: BorderRadius.circular(16),
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: _selectedFile != null ? Colors.green.shade50 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            _selectedFile != null ? Icons.check_circle : Icons.upload_file,
                            color: _selectedFile != null ? Colors.green : Colors.grey,
                            size: 48,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _selectedFile != null ? _fileName : 'Sélectionner un document',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: _selectedFile != null ? FontWeight.bold : FontWeight.normal,
                            color: _selectedFile != null ? Colors.green.shade700 : Colors.grey.shade700,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'PDF, Word ou Excel',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              if (_message.isNotEmpty) ...[
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: _message.contains('succès') ? Colors.green.shade50 : Colors.red.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: _message.contains('succès') ? Colors.green.shade200 : Colors.red.shade200,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        _message.contains('succès') ? Icons.check_circle : Icons.error,
                        color: _message.contains('succès') ? Colors.green.shade700 : Colors.red.shade700,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          _message,
                          style: TextStyle(
                            color: _message.contains('succès') ? Colors.green.shade700 : Colors.red.shade700,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _isLoading ? null : _sendPlanning,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  elevation: 2,
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.send),
                          SizedBox(width: 8),
                          Text('Envoyer le planning', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        ],
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
