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
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_GET['employe_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'employe_id requis']);
    exit;
}

$employe_id = (int)$_GET['employe_id'];

try {
    $stmt = $pdo->prepare('
        SELECT c.*, e.nom, e.prenom 
        FROM conges c 
        JOIN employees e ON c.employe_id = e.id 
        WHERE c.employe_id = ? 
        ORDER BY c.date_demande DESC
    ');
    
    $stmt->execute([$employe_id]);
    $conges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'conges' => $conges
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
}