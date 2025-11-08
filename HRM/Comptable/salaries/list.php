<?php
require '../../includes/auth.php';
require_roles(['RH', 'Comptable']);
require '../../includes/db.php';

// Récupérer tous les salaires avec info employé et département
$stmt = $pdo->query('SELECT s.*, e.nom AS emp_nom, e.prenom AS emp_prenom, e.departement_id, d.nom AS departement_nom FROM salaries s LEFT JOIN employees e ON s.employe_id = e.id LEFT JOIN departments d ON e.departement_id = d.id');
$salaries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des salaires</title>
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
            <div class="d-flex justify-content-between align-items-center py-3 px-2 sticky-top" style="z-index:1035; border-bottom:1px solid #e9ecef; box-shadow:0 2px 4px rgba(0,0,0,0.03); margin:16px 8px 20px 8px;">
                <h1 class="h3 fw-bold mb-0">Liste des salaires</h1>
                <?php if ($_SESSION['role'] === 'Comptable'): ?>
                <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Ajouter un salaire</a>
                <?php endif; ?>
            </div>
            <div class="card shadow-sm animate-fade-in">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:70vh; overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>Employé</th>
                                    <th>Département</th>
                                    <th>Salaire net</th>
                                    <th>Date dernier paiement</th>
                                    <th style="width:260px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $num = 1; foreach ($salaries as $sal): ?>
                                <tr>
                                    <td><?= $num++ ?></td>
                                    <td><?= htmlspecialchars($sal['emp_nom'] . ' ' . $sal['emp_prenom']) ?></td>
                                    <td><?= htmlspecialchars($sal['departement_nom']) ?></td>
                                    <td><?= htmlspecialchars($sal['salaire_net']) ?> <?= htmlspecialchars($sal['monnaie'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($sal['date_dernier_paiement'] ?? '') ?></td>
                                    <td>
                                        <a href="view.php?id=<?= $sal['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="Voir"><i class="bi bi-eye"></i></a>
                                        <?php if ($_SESSION['role'] === 'Comptable'): ?>
                                        <a href="edit.php?id=<?= $sal['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Modifier"><i class="bi bi-pencil"></i></a>
                                        <a href="pay.php?id=<?= $sal['id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Paiement"><i class="bi bi-cash-coin"></i></a>
                                        <a href="delete.php?id=<?= $sal['id'] ?>" class="btn btn-sm btn-outline-danger me-1" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce salaire ?');"><i class="bi bi-trash"></i></a>
                                        <?php endif; ?>
                                        <a href="history.php?id=<?= $sal['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Historique"><i class="bi bi-clock-history"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
        </main>
    </div>
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