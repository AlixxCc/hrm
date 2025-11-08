<?php
error_reporting(0);
ini_set('display_errors', 0);

ob_start();

try {
    require '../includes/db.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erreur de connexion DB: ' . $e->getMessage()]);
    exit;
}

ob_end_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required = ['numero_conge', 'statut', 'commentaire_rh'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Donnée manquante : ' . $field]);
        exit;
    }
}

$numero_conge = $data['numero_conge'];
$statut = $data['statut'];
$commentaire_rh = $data['commentaire_rh'];
$date_traitement = date('Y-m-d H:i:s');

try {
    // Mettre à jour le statut de la demande
    $stmt = $pdo->prepare('
        UPDATE conges 
        SET statut = ?, 
            commentaire_rh = ?, 
            date_traitement = ?
        WHERE numero_conge = ?
    ');
    
    $stmt->execute([$statut, $commentaire_rh, $date_traitement, $numero_conge]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Demande de congé non trouvée']);
        exit;
    }

    // Récupérer les informations de la demande mise à jour
    $stmt = $pdo->prepare('
        SELECT c.*, e.nom, e.prenom
        FROM conges c
        JOIN employees e ON c.employe_id = e.id
        WHERE c.numero_conge = ?
    ');
    $stmt->execute([$numero_conge]);
    $conge = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Statut de la demande mis à jour avec succès',
        'conge' => $conge
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
}