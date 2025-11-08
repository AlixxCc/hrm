import 'package:flutter/material.dart';
import 'leave_detail_page.dart';

class NotificationItem {
  final int congeId;
  final String numeroConge;
  final String statut;
  final String message;
  final Map<String, dynamic> conge;
  bool lu;

  NotificationItem({
    required this.congeId,
    required this.numeroConge,
    required this.statut,
    required this.message,
    required this.conge,
    this.lu = false,
  });
}

class NotificationsPage extends StatelessWidget {
  final List<NotificationItem> notifications;
  final Function(NotificationItem) onNotificationRead;

  const NotificationsPage({
    Key? key,
    required this.notifications,
    required this.onNotificationRead,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    if (notifications.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Notifications')),
        body: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.notifications_none, size: 64, color: Colors.grey),
              SizedBox(height: 16),
              Text(
                'Aucune notification',
                style: TextStyle(fontSize: 18, color: Colors.grey),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: notifications.length,
        separatorBuilder: (context, index) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          final notif = notifications[index];
          return Card(
            elevation: notif.lu ? 1 : 3,
            color: notif.lu ? Colors.white : Colors.blue[50],
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () {
                onNotificationRead(notif);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => LeaveDetailPage(
                      conge: notif.conge,
                      onRead: () => onNotificationRead(notif),
                    ),
                  ),
                );
              },
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(
                      notif.statut == 'Accepté'
                          ? Icons.check_circle
                          : Icons.cancel,
                      color: notif.statut == 'Accepté'
                          ? Colors.green
                          : Colors.red,
                      size: 32,
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Congé ${notif.statut}',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            notif.message,
                            style: const TextStyle(fontSize: 14),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'N° ${notif.numeroConge}',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (!notif.lu)
                      Container(
                        width: 10,
                        height: 10,
                        decoration: const BoxDecoration(
                          color: Colors.blue,
                          shape: BoxShape.circle,
                        ),
                      ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
