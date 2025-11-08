<?php
require '../includes/db.php';
// require '../includes/auth.php'; // À activer pour sécuriser l’API

header('Content-Type: application/json');

// Vérifier que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['employee_id'], $data['titre'], $data['contenu'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

$employee_id = (int)$data['employee_id'];
$titre = $data['titre'];
$contenu = $data['contenu'];
$date = date('Y-m-d H:i:s');

// Enregistrer le rapport
$stmt = $pdo->prepare('INSERT INTO rapports (employee_id, titre, contenu, date_rapport) VALUES (?, ?, ?, ?)');
if ($stmt->execute([$employee_id, $titre, $contenu, $date])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
} 