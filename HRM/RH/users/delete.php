<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Supprimer l'utilisateur
$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php');
exit; 