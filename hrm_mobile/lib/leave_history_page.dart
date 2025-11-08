import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'leave_detail_page.dart';

class LeaveHistoryPage extends StatefulWidget {
  final int employeId;
  const LeaveHistoryPage({Key? key, required this.employeId}) : super(key: key);
  @override
  State<LeaveHistoryPage> createState() => _LeaveHistoryPageState();
}

class _LeaveHistoryPageState extends State<LeaveHistoryPage> {
  List<dynamic> conges = [];
  bool isLoading = true;
  String error = '';

  @override
  void initState() {
    super.initState();
    fetchLeaveHistory();
  }

  Future<void> fetchLeaveHistory() async {
    try {
      final response = await http.get(
        Uri.parse('http://172.20.10.3/HRM/HRM/api/historique_conges.php?employe_id=${widget.employeId}'),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          setState(() {
            conges = data['conges'];
            isLoading = false;
          });
        } else {
          setState(() {
            error = data['error'] ?? 'Erreur inconnue';
            isLoading = false;
          });
        }
      } else {
        setState(() {
          error = 'Erreur serveur';
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        error = 'Erreur réseau : $e';
        isLoading = false;
      });
    }
  }

  String _formatDate(String date) {
    final DateTime dateTime = DateTime.parse(date);
    return '${dateTime.day.toString().padLeft(2, '0')}/${dateTime.month.toString().padLeft(2, '0')}/${dateTime.year}';
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'en attente':
        return Colors.orange;
      case 'accepté':
        return Colors.green;
      case 'refusé':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'en attente':
        return Icons.hourglass_top_rounded;
      case 'accepté':
        return Icons.check_circle_rounded;
      case 'refusé':
        return Icons.cancel_rounded;
      default:
        return Icons.info_outline_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique des congés'),
        elevation: 1,
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : error.isNotEmpty
                ? Center(child: Text(error, style: const TextStyle(color: Colors.red)))
                : conges.isEmpty
                    ? const Center(child: Text('Aucune demande de congé trouvée'))
                    : ListView.separated(
                        itemCount: conges.length,
                        separatorBuilder: (context, i) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final conge = conges[index];
                          return Card(
                            elevation: 2,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => LeaveDetailPage(conge: conge),
                                  ),
                                );
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(20),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Icon(_getStatusIcon(conge['statut']),
                                        color: _getStatusColor(conge['statut']), size: 32),
                                    const SizedBox(width: 18),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            'Congé ${conge['nature']}',
                                            style: Theme.of(context).textTheme.titleMedium,
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Du ${_formatDate(conge['date_debut'])} au ${_formatDate(conge['date_fin'])}',
                                            style: Theme.of(context).textTheme.bodyMedium,
                                          ),
                                          const SizedBox(height: 8),
                                          Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                                decoration: BoxDecoration(
                                                  color: _getStatusColor(conge['statut']).withOpacity(0.1),
                                                  borderRadius: BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color: _getStatusColor(conge['statut']),
                                                  ),
                                                ),
                                                child: Row(
                                                  children: [
                                                    Icon(_getStatusIcon(conge['statut']),
                                                        color: _getStatusColor(conge['statut']), size: 16),
                                                    const SizedBox(width: 4),
                                                    Text(
                                                      conge['statut'],
                                                      style: TextStyle(
                                                        color: _getStatusColor(conge['statut']),
                                                        fontWeight: FontWeight.bold,
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}
