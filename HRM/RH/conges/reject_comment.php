<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: list.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

// Récupérer la demande pour afficher infos
$stmt = $pdo->prepare('SELECT c.*, e.nom, e.prenom FROM conges c JOIN employees e ON c.employe_id = e.id WHERE c.id = ?');
$stmt->execute([$id]);
$conge = $stmt->fetch();
if (!$conge) {
    header('Location: list.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentaire = trim($_POST['commentaire'] ?? '');
    if ($commentaire === '') {
        $error = 'Le commentaire est obligatoire.';
    } else {
        // Appeler l'API pour mettre à jour le statut et le commentaire
        $apiUrl = 'http://localhost/HRM/HRM/api/reponse_conge.php';
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'numero_conge' => $conge['numero_conge'],
            'statut' => 'Refusé',
            'commentaire_rh' => $commentaire
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
        header('Location: view.php?id=' . $id . '&success=refused');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Refuser la demande de congé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../../custom.css" rel="stylesheet">
</head>
<body class="bg-light" id="body-root">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="flex-grow-1" style="margin-left:240px; padding-top:56px; min-height:100vh;">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center py-3 px-2" style="margin:16px 8px 20px 8px;">
                <h1 class="h3 fw-bold mb-0">Refuser la demande de congé</h1>
                <a href="view.php?id=<?= $conge['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour</a>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <p><strong>Employé :</strong> <?= htmlspecialchars($conge['nom'] . ' ' . $conge['prenom']) ?></p>
                    <p><strong>Période :</strong> <?= htmlspecialchars($conge['date_debut']) ?> — <?= htmlspecialchars($conge['date_fin']) ?></p>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $conge['id'] ?>">
                        <div class="mb-3">
                            <label for="commentaire" class="form-label">Commentaire de refus <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="4" required><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Refuser la demande</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
