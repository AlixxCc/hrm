<?php
// Copy this file to 'app_config.php' and set your Flutter app webhook URL.
// Example:
// $EMPLOYEE_APP_WEBHOOK = 'https://your-app-backend.example/api/notify_conge';

$EMPLOYEE_APP_WEBHOOK = '';

// Optionally, to secure webhook requests, set a shared secret. The server RH will
// add an 'X-Signature: sha256=<hmac>' header to webhook requests which your backend
// can verify using the same secret.
$NOTIFY_SHARED_SECRET = '';

// Optionally, you can send push notifications directly via Firebase Cloud Messaging (FCM).
// To use FCM topic messaging, set your server key here and have the mobile app subscribe
// to topic "employee_{id}" where {id} is the employee id.
// Example:
// $FCM_SERVER_KEY = 'AAAA...';
$FCM_SERVER_KEY = '';
