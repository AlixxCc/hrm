<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: list.php?error=missing_id');
    exit;
}

// Vérifier que le département existe
$stmt = $pdo->prepare('SELECT * FROM departments WHERE id = ?');
$stmt->execute([$id]);
$dept = $stmt->fetch();
if (!$dept) {
    header('Location: list.php?error=not_found');
    exit;
}

// Vérifier qu'il n'y a pas d'employés associés
$stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE departement_id = ?');
$stmt->execute([$id]);
$nb_employes = $stmt->fetchColumn();
if ($nb_employes > 0) {
    header('Location: list.php?error=has_employees');
    exit;
}

// Supprimer le département
$stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php?success=deleted');
exit; 