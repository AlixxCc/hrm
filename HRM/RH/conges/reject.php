<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer les informations du congé
    $get = $pdo->prepare('SELECT * FROM conges WHERE id = ?');
    $get->execute([$id]);
    $conge = $get->fetch();

    if (!$conge) {
        header('Location: list.php');
        exit;
    }

    // Appeler l'API pour mettre à jour le statut
    $apiUrl = 'http://localhost/HRM/HRM/api/reponse_conge.php';
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'numero_conge' => $conge['numero_conge'],
        'statut' => 'Refusé',
        'commentaire_rh' => 'Demande refusée par RH'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $result = curl_exec($ch);
    curl_close($ch);

    header('Location: view.php?id=' . $id . '&success=refused');
    exit;
