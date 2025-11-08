<?php
require '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$employe_id = isset($_GET['employe_id']) ? (int)$_GET['employe_id'] : 0;
if ($employe_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre employe_id requis']);
    exit;
}

$stmt = $pdo->prepare('SELECT e.fonction,
                               e.lieu_affectation,
                               e.telephone_local,
                               e.telephone_whatsapp,
                               COALESCE(e.email_professionnel, e.email) AS email,
                               d.nom AS service
                        FROM employees e
                        LEFT JOIN departments d ON d.id = e.departement_id
                        WHERE e.id = ?');
$stmt->execute([$employe_id]);
$emp = $stmt->fetch();

if (!$emp) {
    http_response_code(404);
    echo json_encode(['error' => "Employé introuvable"]);
    exit;
}

$telephone = $emp['telephone_local'] ?: $emp['telephone_whatsapp'];
$zone_intervention = $emp['lieu_affectation'];

echo json_encode([
    'success' => true,
    'employe_id' => $employe_id,
    'fonction' => $emp['fonction'] ?? '',
    'service' => $emp['service'] ?? '',
    'zone_intervention' => $zone_intervention ?? '',
    'telephone' => $telephone ?? '',
    'email' => $emp['email'] ?? ''
]);
?>




















