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
$mot_de_passe = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

// Vérifier que l'employé existe
$stmt = $pdo->prepare('SELECT id FROM employees WHERE email = ?');
$stmt->execute([$email]);
$emp = $stmt->fetch();
if (!$emp) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun employé avec cet email']);
    exit;
}

// Vérifier que l'utilisateur mobile n'existe pas déjà
$stmt = $pdo->prepare('SELECT id FROM users_mobile WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => 'Compte déjà créé']);
    exit;
}

// Créer le compte mobile
$stmt = $pdo->prepare('INSERT INTO users_mobile (employe_id, email, mot_de_passe) VALUES (?, ?, ?)');
$stmt->execute([$emp['id'], $email, $mot_de_passe]);
echo json_encode(['success' => true]); 