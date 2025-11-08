<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT c.*, e.nom, e.prenom, e.email AS emp_email FROM conges c JOIN employees e ON c.employe_id = e.id WHERE c.id = ?');
$stmt->execute([$id]);
$conge = $stmt->fetch();
if (!$conge) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la demande de congé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../../custom.css" rel="stylesheet">
</head>
<body class="bg-light" id="body-root">
    <?php include '../../includes/sidebar.php'; ?>
    <header class="navbar shadow-sm align-items-center bg-white rounded-3 py-2 px-4"
        style="position:fixed; left:240px; right:24px; top:0; height:56px; min-height:56px; z-index:1040; border:none; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin:12px 0 16px 24px;">
        <div class="ms-auto d-flex align-items-center">
            <button id="theme-toggle" class="btn btn-outline-primary me-2" title="Changer de mode">
                <span id="theme-icon" class="bi bi-moon"></span>
            </button>
        </div>
    </header>
    <main class="flex-grow-1" style="margin-left:240px; padding-top:56px; min-height:100vh;">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center py-3 px-2" style="z-index:1035; border-bottom:1px solid #e9ecef; box-shadow:0 2px 4px rgba(0,0,0,0.03); margin:16px 8px 20px 8px;">
                <h1 class="h3 fw-bold mb-0">Détails de la demande de congé</h1>
                <div>
                    <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
                </div>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'accepted'): ?>
                <div class="alert alert-success mt-3">Demande acceptée et document téléversé.</div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'refused'): ?>
                <div class="alert alert-warning mt-3">Demande refusée.</div>
            <?php endif; ?>

            <div class="card shadow-sm animate-fade-in">
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Employé :</strong> <?= htmlspecialchars($conge['nom'].' '.$conge['prenom']) ?></li>
                        <li class="list-group-item"><strong>Fonction :</strong> <?= htmlspecialchars($conge['fonction']) ?></li>
                        <li class="list-group-item"><strong>Service :</strong> <?= htmlspecialchars($conge['service']) ?></li>
                        <li class="list-group-item"><strong>Zone d'intervention :</strong> <?= htmlspecialchars($conge['zone_intervention']) ?></li>
                        <li class="list-group-item"><strong>Téléphone employé :</strong> <?= htmlspecialchars($conge['telephone']) ?></li>
                        <li class="list-group-item"><strong>Email employé :</strong> <?= htmlspecialchars($conge['emp_email']) ?></li>
                        <li class="list-group-item"><strong>Numéro congé :</strong> <?= htmlspecialchars($conge['numero_conge']) ?></li>
                        <li class="list-group-item"><strong>Date début :</strong> <?= htmlspecialchars($conge['date_debut']) ?></li>
                        <li class="list-group-item"><strong>Date fin :</strong> <?= htmlspecialchars($conge['date_fin']) ?></li>
                        <li class="list-group-item"><strong>Nature :</strong> <?= htmlspecialchars($conge['nature']) ?></li>
                        <li class="list-group-item"><strong>Motif :</strong> <?= nl2br(htmlspecialchars($conge['motif'])) ?></li>
                        <li class="list-group-item"><strong>Statut :</strong> <?= htmlspecialchars($conge['statut']) ?></li>
                        <?php if (!empty($conge['commentaire_rh'])): ?>
                        <li class="list-group-item"><strong>Commentaire RH :</strong> <?= nl2br(htmlspecialchars($conge['commentaire_rh'])) ?></li>
                        <?php endif; ?>
                        <li class="list-group-item"><strong>Date de demande :</strong> <?= htmlspecialchars($conge['date_demande']) ?></li>
                    </ul>
                    <div class="d-flex justify-content-end">
                        <?php if (!in_array($conge['statut'], ['Accepté', 'Refusé'])): ?>
                            <a href="accept.php?id=<?= $conge['id'] ?>" class="btn btn-success me-2" title="Accepter"><i class="bi bi-check2-circle"></i> Accepter</a>
                            <a href="reject_comment.php?id=<?= $conge['id'] ?>" class="btn btn-danger me-3" title="Refuser"><i class="bi bi-x-circle"></i> Refuser</a>
                        <?php endif; ?>
                        <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour à la liste</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const themeToggle = document.getElementById('theme-toggle');
    const bodyRoot = document.getElementById('body-root');
    const themeIcon = document.getElementById('theme-icon');
    const sidebar = document.getElementById('sidebar');
    if (localStorage.getItem('theme') === 'dark') {
        bodyRoot.classList.remove('bg-light');
        bodyRoot.classList.add('bg-dark', 'text-light');
        themeIcon.classList.remove('bi-moon');
        themeIcon.classList.add('bi-sun');
        sidebar.classList.add('sidebar-dark');
    }
    themeToggle.addEventListener('click', () => {
        if (bodyRoot.classList.contains('bg-light')) {
            bodyRoot.classList.remove('bg-light');
            bodyRoot.classList.add('bg-dark', 'text-light');
            themeIcon.classList.remove('bi-moon');
            themeIcon.classList.add('bi-sun');
            sidebar.classList.add('sidebar-dark');
            localStorage.setItem('theme', 'dark');
        } else {
            bodyRoot.classList.remove('bg-dark', 'text-light');
            bodyRoot.classList.add('bg-light');
            themeIcon.classList.remove('bi-sun');
            themeIcon.classList.add('bi-moon');
            sidebar.classList.remove('sidebar-dark');
            localStorage.setItem('theme', 'light');
        }
    });
    </script>
</body>
</html>
