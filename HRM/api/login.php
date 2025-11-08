<?php
require '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'], $data['mot_de_passe'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email et mot de passe requis']);
    exit;
}
$email = $data['email'];
$mot_de_passe = $data['mot_de_passe'];

$stmt = $pdo->prepare('SELECT employe_id, mot_de_passe FROM users_mobile WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Identifiants invalides']);
    exit;
}

echo json_encode(['success' => true, 'employe_id' => $user['employe_id']]); 