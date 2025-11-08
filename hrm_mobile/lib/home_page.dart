import 'package:flutter/material.dart';
import 'request_leave_page.dart';
import 'notifications_page.dart';
import 'leave_history_page.dart';
import 'send_report_page.dart';
import 'send_timesheet_page.dart';
import 'send_planning_page.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class HomePage extends StatefulWidget {
  final int employeId;
  
  const HomePage({Key? key, required this.employeId}) : super(key: key);
  
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;
  List<dynamic> _conges = [];
  List<NotificationItem> _notifications = [];
  Set<String> _readNotifIds = {};

  @override
  void initState() {
    super.initState();
    _loadReadNotifications();
    _fetchCongesAndDetectNotifications();
  }

  Future<void> _loadReadNotifications() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _readNotifIds = (prefs.getStringList('read_notif_ids') ?? []).toSet();
    });
  }

  Future<void> _saveReadNotifications() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList('read_notif_ids', _readNotifIds.toList());
  }

  Future<void> _fetchCongesAndDetectNotifications() async {
    final url = Uri.parse('http://172.20.10.3/HRM/HRM/api/historique_conges.php?employe_id=${widget.employeId}');
    try {
      final response = await http.get(url);
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          setState(() {
            _conges = data['conges'];
            _notifications = _conges
                .where((c) => (c['statut'] == 'Accepté' || c['statut'] == 'Refusé'))
                .map<NotificationItem>((c) => NotificationItem(
                      congeId: int.parse(c['id'].toString()),
                      numeroConge: c['numero_conge'],
                      statut: c['statut'],
                      message: c['statut'] == 'Accepté'
                          ? 'Votre demande a été acceptée.'
                          : 'Votre demande a été refusée.',
                      conge: c,
                      lu: _readNotifIds.contains(c['numero_conge']),
                    ))
                .toList();
          });
        }
      }
    } catch (e) {
      // ignore erreur réseau pour la démo
    }
  }

  void _onNotificationRead(NotificationItem notif) async {
    setState(() {
      notif.lu = true;
      _readNotifIds.add(notif.numeroConge);
    });
    await _saveReadNotifications();
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  Widget _buildHomeScreen() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [const Color(0xFF0d6efd), const Color(0xFF0d6efd).withOpacity(0.8)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0d6efd).withOpacity(0.3),
                  blurRadius: 15,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: const Row(
              children: [
                Icon(Icons.business_center, color: Colors.white, size: 40),
                SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Bienvenue sur',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 14,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'HRM Mobile',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Actions rapides',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildQuickActionCard(
            title: 'Historique des congés',
            subtitle: 'Voir vos demandes de congés',
            icon: Icons.history,
            color: Colors.blue,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => LeaveHistoryPage(employeId: widget.employeId),
                ),
              );
            },
          ),
          const SizedBox(height: 12),
          _buildQuickActionCard(
            title: 'Envoyer un rapport',
            subtitle: 'Rapport mensuel',
            icon: Icons.analytics,
            color: Colors.green,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => SendReportPage(employeId: widget.employeId),
                ),
              );
            },
          ),
          const SizedBox(height: 12),
          _buildQuickActionCard(
            title: 'Envoyer un timesheet',
            subtitle: 'Feuille de temps mensuelle',
            icon: Icons.access_time,
            color: Colors.purple,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => SendTimesheetPage(employeId: widget.employeId),
                ),
              );
            },
          ),
          const SizedBox(height: 12),
          _buildQuickActionCard(
            title: 'Envoyer un planning',
            subtitle: 'Planning hebdomadaire',
            icon: Icons.event_note,
            color: Colors.orange,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => SendPlanningPage(employeId: widget.employeId),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 28),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey.shade400),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    List<Widget> _pages = <Widget>[
      _buildHomeScreen(),
      RequestLeavePage(employeId: widget.employeId),
      NotificationsPage(
        notifications: _notifications,
        onNotificationRead: _onNotificationRead,
      ),
    ];
    
    int unreadCount = _notifications.where((n) => !n.lu).length;
    
    return Scaffold(
      body: _pages.elementAt(_selectedIndex),
      bottomNavigationBar: BottomNavigationBar(
        items: <BottomNavigationBarItem>[
          const BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'Accueil',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.calendar_today),
            label: 'Congés',
          ),
          BottomNavigationBarItem(
            icon: Stack(
              children: [
                const Icon(Icons.notifications),
                if (unreadCount > 0)
                  Positioned(
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 16,
                        minHeight: 16,
                      ),
                      child: Text(
                        '$unreadCount',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
            label: 'Notifications',
          ),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: const Color(0xFF0d6efd),
        onTap: _onItemTapped,
      ),
    );
  }
}
