<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM conges WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: list.php?success=deleted');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Supprimer la demande de congé (suppression via lien GET)
$stmt = $pdo->prepare('DELETE FROM conges WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php');
exit;
