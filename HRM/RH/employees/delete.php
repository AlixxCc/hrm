<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    // Supprimer l'employé
    $stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: list.php?success=deleted');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Supprimer l'employé
$stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php');
exit; 