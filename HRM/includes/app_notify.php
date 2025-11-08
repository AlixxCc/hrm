<?php
// Optional helper to notify the employee's mobile app via a webhook.
// Create 'includes/app_config.php' and set $EMPLOYEE_APP_WEBHOOK = 'https://your-app-endpoint.example/notify';

function notify_employee_app(array $payload): array {
    $result = ['sent' => false, 'http_code' => null, 'response' => null, 'error' => null, 'method' => null];
    $configFile = __DIR__ . '/app_config.php';
    if (!file_exists($configFile)) {
        $result['error'] = 'No app_config.php configured';
        return $result;
    }

    include $configFile; // may define $EMPLOYEE_APP_WEBHOOK and/or $FCM_SERVER_KEY

    // 1) If webhook is configured, prefer calling it (app can handle push itself)
    if (!empty($EMPLOYEE_APP_WEBHOOK)) {
        $result['method'] = 'webhook';
        $ch = curl_init($EMPLOYEE_APP_WEBHOOK);
        $payloadJson = json_encode($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        // If a shared secret is configured, add an HMAC signature header for verification
        if (!empty($NOTIFY_SHARED_SECRET)) {
            $hmac = hash_hmac('sha256', $payloadJson, $NOTIFY_SHARED_SECRET);
            $headers[] = 'X-Signature: sha256=' . $hmac;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        // timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $resp = curl_exec($ch);
        if ($resp === false) {
            $result['error'] = curl_error($ch);
        }
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['response'] = $resp;
        $result['sent'] = ($resp !== false);
        curl_close($ch);
        return $result;
    }

    // 2) Otherwise, if FCM server key is configured, send a topic push to employee_{id}
    if (!empty($FCM_SERVER_KEY) && isset($payload['employe_id'])) {
        $result['method'] = 'fcm';
        $topic = 'employee_' . intval($payload['employe_id']);
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

        $notification = [
            'title' => $payload['title'] ?? 'Notification RH',
            'body' => $payload['message'] ?? '',
        ];

        $data = $payload;

        $fcmPayload = [
            'to' => '/topics/' . $topic,
            'notification' => $notification,
            'data' => $data,
        ];

        $ch = curl_init($fcmUrl);
        $payloadJson = json_encode($fcmPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: key=' . $FCM_SERVER_KEY,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $resp = curl_exec($ch);
        if ($resp === false) {
            $result['error'] = curl_error($ch);
        }
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['response'] = $resp;
        $result['sent'] = ($resp !== false);
        curl_close($ch);
        return $result;
    }

    $result['error'] = 'No notification method configured (set EMPLOYEE_APP_WEBHOOK or FCM_SERVER_KEY in includes/app_config.php)';
    return $result;
}
