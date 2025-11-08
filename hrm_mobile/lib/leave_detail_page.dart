import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class LeaveDetailPage extends StatefulWidget {
  final Map<String, dynamic> conge;
  final VoidCallback? onRead;
  const LeaveDetailPage({Key? key, required this.conge, this.onRead}) : super(key: key);

  @override
  State<LeaveDetailPage> createState() => _LeaveDetailPageState();
}

class _LeaveDetailPageState extends State<LeaveDetailPage> {
  @override
  void initState() {
    super.initState();
    widget.onRead?.call();
  }

  String _formatDate(String date) {
    final DateTime dateTime = DateTime.parse(date);
    return '${dateTime.day.toString().padLeft(2, '0')}/${dateTime.month.toString().padLeft(2, '0')}/${dateTime.year}';
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'accepté':
        return Colors.green;
      case 'refusé':
        return Colors.red;
      case 'en attente':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  Future<void> _openDocument(BuildContext context, String docPath) async {
    final url = 'http://172.20.10.3/HRM/HRM/$docPath';
    if (await canLaunchUrl(Uri.parse(url))) {
      await launchUrl(Uri.parse(url));
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Impossible d\'ouvrir le document')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final conge = widget.conge;
    bool hasDocument = conge['commentaire_rh'] != null && 
                      conge['commentaire_rh'].toString().contains('Document RH:');
    String? docPath;
    if (hasDocument) {
      docPath = conge['commentaire_rh']
          .toString()
          .split('Document RH:')
          .last
          .trim();
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails du congé'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Congé ${conge['nature']}',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Du ${_formatDate(conge['date_debut'])} au ${_formatDate(conge['date_fin'])}',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 16),
                    Text('Motif : ${conge['motif']}'),
                    const SizedBox(height: 8),
                    Text('Zone : ${conge['zone_intervention']}'),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: _getStatusColor(conge['statut']).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: _getStatusColor(conge['statut']),
                        ),
                      ),
                      child: Text(
                        conge['statut'],
                        style: TextStyle(
                          color: _getStatusColor(conge['statut']),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            if (conge['statut'] == 'Accepté' && hasDocument) ...[
              const SizedBox(height: 24),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Document RH',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () => _openDocument(context, docPath!),
                        icon: const Icon(Icons.description),
                        label: const Text('Voir le document'),
                      ),
                    ],
                  ),
                ),
              ),
            ],
            if (conge['commentaire_rh'] != null && 
                !conge['commentaire_rh'].toString().contains('Document RH:')) ...[
              const SizedBox(height: 24),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Commentaire RH',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 8),
                      Text(conge['commentaire_rh'].toString()),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
