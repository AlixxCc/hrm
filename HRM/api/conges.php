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

$required = ['employe_id', 'zone_intervention', 'date_debut', 'date_fin', 'nature', 'motif'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Donnée manquante : ' . $field]);
        exit;
    }
}

$employe_id = (int)$data['employe_id'];
$zone_intervention = $data['zone_intervention'];
$date_debut = $data['date_debut'];
$date_fin = $data['date_fin'];
$nature = $data['nature'];
$motif = $data['motif'];

try {
    $stmt = $pdo->prepare('
        SELECT e.nom, e.prenom, e.email, e.fonction, e.telephone_whatsapp, d.nom as departement
        FROM employees e
        LEFT JOIN departments d ON e.departement_id = d.id
        WHERE e.id = ?
    ');
    $stmt->execute([$employe_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo json_encode(['success' => false, 'error' => 'Employé non trouvé']);
        exit;
    }
    
    $fonction = $employee['fonction'] ?? '';
    $service = $employee['departement'] ?? '';
    $telephone = $employee['telephone_whatsapp'] ?? '';
    $email = $employee['email'] ?? '';
    
    $numero_conge = 'CONGE-' . date('Ymd-His') . '-' . $employe_id;
    $statut = 'en attente';
    $commentaire_rh = null;
    $date_demande = date('Y-m-d H:i:s');
    $date_traitement = null;

    $insertStmt = $pdo->prepare('
        INSERT INTO conges (
            employe_id, fonction, service, zone_intervention, telephone, email, 
            numero_conge, date_debut, date_fin, nature, motif, statut, 
            commentaire_rh, date_demande, date_traitement
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $insertStmt->execute([
        $employe_id, 
        $fonction,
        $service,
        $zone_intervention, 
        $telephone,
        $email,
        $numero_conge, 
        $date_debut, 
        $date_fin, 
        $nature, 
        $motif, 
        $statut, 
        $commentaire_rh, 
        $date_demande, 
        $date_traitement
    ]);
    
    echo json_encode([
        'success' => true, 
        'numero_conge' => $numero_conge,
        'message' => 'Demande de congé créée avec succès'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
}