<?php
require '../../includes/auth.php';
require_roles(['RH', 'Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id']) || !isset($_GET['salaire_id'])) {
    header('Location: list.php');
    exit;
}
$payment_id = (int)$_GET['id'];
$salaire_id = (int)$_GET['salaire_id'];

// Supprimer le paiement
$stmt = $pdo->prepare('DELETE FROM salary_payments WHERE id = ?');
$stmt->execute([$payment_id]);

// Vérifier s'il reste des paiements pour ce salaire
$stmt = $pdo->prepare('SELECT MAX(date_paiement) AS last_date FROM salary_payments WHERE salaire_id = ?');
$stmt->execute([$salaire_id]);
$row = $stmt->fetch();
$last_date = $row && $row['last_date'] ? $row['last_date'] : null;

// Mettre à jour la date_dernier_paiement dans salaries
$stmt = $pdo->prepare('UPDATE salaries SET date_dernier_paiement = ? WHERE id = ?');
$stmt->execute([$last_date, $salaire_id]);

header('Location: history.php?id=' . $salaire_id);
exit; 