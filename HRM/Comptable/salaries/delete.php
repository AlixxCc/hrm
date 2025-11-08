<?php
require '../../includes/auth.php';
require_roles(['Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Supprimer le salaire
$stmt = $pdo->prepare('DELETE FROM salaries WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php');
exit; 