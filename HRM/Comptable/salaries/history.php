<?php
require '../../includes/auth.php';
require_roles(['RH', 'Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer le salaire et l'employé
$stmt = $pdo->prepare('SELECT s.*, e.nom AS emp_nom, e.prenom AS emp_prenom FROM salaries s LEFT JOIN employees e ON s.employe_id = e.id WHERE s.id = ?');
$stmt->execute([$id]);
$sal = $stmt->fetch();
if (!$sal) {
    header('Location: list.php');
    exit;
}

// Récupérer l'historique des paiements
$stmt = $pdo->prepare('SELECT * FROM salary_payments WHERE salaire_id = ? ORDER BY date_paiement DESC');
$stmt->execute([$id]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des paiements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                <h1 class="h3 fw-bold mb-0">Historique des paiements pour <?= htmlspecialchars($sal['emp_nom'] . ' ' . $sal['emp_prenom']) ?></h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div class="card shadow-sm animate-fade-in mt-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date de paiement</th>
                                    <th>Période</th>
                                    <th>Montant total</th>
                                    <th>Méthode de paiement</th>
                                    <?php if ($_SESSION['role'] === 'Comptable'): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pay['date_paiement'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($pay['periode'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($pay['montant_total'] ?? '') ?> <?= htmlspecialchars($sal['monnaie'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($pay['methode_paiement'] ?? '') ?></td>
                                    <?php if ($_SESSION['role'] === 'Comptable'): ?>
                                    <td>
                                        <a href="edit_payment.php?id=<?= $pay['id'] ?>&salaire_id=<?= $id ?>" class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></a>
                                        <a href="delete_payment.php?id=<?= $pay['id'] ?>&salaire_id=<?= $id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce paiement ?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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