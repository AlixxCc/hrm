<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer l'employé avec son département
$stmt = $pdo->prepare('SELECT e.*, d.nom AS departement_nom FROM employees e LEFT JOIN departments d ON e.departement_id = d.id WHERE e.id = ?');
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'employé</title>
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
                <h1 class="h3 fw-bold mb-0">Détail de l'employé</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div class="card shadow-sm animate-fade-in">
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($emp['nom']) ?></li>
                        <li class="list-group-item"><strong>Prénom :</strong> <?= htmlspecialchars($emp['prenom']) ?></li>
                        <li class="list-group-item"><strong>Email :</strong> <?= htmlspecialchars($emp['email']) ?></li>
                        <li class="list-group-item"><strong>Email professionnel :</strong> <?= htmlspecialchars($emp['email_professionnel']) ?></li>
                        <li class="list-group-item"><strong>Téléphone WhatsApp :</strong> <?= htmlspecialchars($emp['telephone_whatsapp']) ?></li>
                        <li class="list-group-item"><strong>Téléphone local :</strong> <?= htmlspecialchars($emp['telephone_local']) ?></li>
                        <li class="list-group-item"><strong>Adresse :</strong> <?= htmlspecialchars($emp['adresse']) ?></li>
                        <li class="list-group-item"><strong>Lieu d'affectation :</strong> <?= htmlspecialchars($emp['lieu_affectation']) ?></li>
                        <li class="list-group-item"><strong>Diplôme :</strong> <?= htmlspecialchars($emp['diplome']) ?></li>
                        <li class="list-group-item"><strong>Fonction :</strong> <?= htmlspecialchars($emp['fonction'] ?? '') ?></li>
                        <li class="list-group-item"><strong>Salaire net :</strong> <?= htmlspecialchars($emp['salaire_net']) ?> Fbu</li>
                        <li class="list-group-item"><strong>Projet d'affectation :</strong> <?= htmlspecialchars($emp['projet_affectation']) ?></li>
                        <li class="list-group-item"><strong>Département :</strong> <?= htmlspecialchars($emp['departement_nom']) ?></li>
                        <li class="list-group-item"><strong>Contrat :</strong> <?php if ($emp['contrat']): ?><a href="../../<?= htmlspecialchars($emp['contrat']) ?>" target="_blank">Télécharger</a><?php else: ?>Aucun<?php endif; ?></li>
                        <li class="list-group-item"><strong>Date de début du contrat :</strong> <?= htmlspecialchars($emp['date_debut_contrat'] ?? '') ?></li>
                        <li class="list-group-item"><strong>Date de fin du contrat :</strong> <?= htmlspecialchars($emp['date_fin_contrat'] ?? '') ?></li>
                    </ul>
                    <div class="d-flex justify-content-end">
                        <a href="edit.php?id=<?= $emp['id'] ?>" class="btn btn-primary me-2"><i class="bi bi-pencil"></i> Modifier</a>
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