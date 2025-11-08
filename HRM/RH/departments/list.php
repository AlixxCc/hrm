<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

// Récupérer tous les départements avec le nombre d'employés
$stmt = $pdo->query('SELECT d.*, (SELECT COUNT(*) FROM employees e WHERE e.departement_id = d.id) AS nb_employes FROM departments d');
$departments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des départements</title>
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
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3">
                    <?php if ($_GET['error'] === 'has_employees'): ?>
                        Impossible de supprimer ce département car il contient encore des employés.
                    <?php elseif ($_GET['error'] === 'not_found'): ?>
                        Département introuvable.
                    <?php elseif ($_GET['error'] === 'missing_id'): ?>
                        Identifiant du département manquant.
                    <?php else: ?>
                        Erreur lors de la suppression du département.
                    <?php endif; ?>
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
                <div class="alert alert-success mt-3">Département supprimé avec succès.</div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center py-3 px-2" style="z-index:1035; border-bottom:1px solid #e9ecef; box-shadow:0 2px 4px rgba(0,0,0,0.03); margin:16px 8px 20px 8px;">
                <h1 class="h3 fw-bold mb-0">Liste des départements</h1>
                <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Ajouter un département</a>
            </div>
            <div class="card shadow-sm animate-fade-in">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:70vh; overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px;">#</th>
                                        <th>Intitulé</th>
                                        <th>Nombre d'employés</th>
                                        <th style="width:220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $num = 1; foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><?= $num++ ?></td>
                                        <td><?= htmlspecialchars($dept['nom']) ?></td>
                                        <td><?= (int)$dept['nb_employes'] ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Modifier"><i class="bi bi-pencil"></i></a>
                                            <a href="delete.php?id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-danger me-1" title="Supprimer" onclick="return confirm('Supprimer ce département ?');"><i class="bi bi-trash"></i></a>
                                            <a href="view_employees.php?id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-info" title="Voir les employés"><i class="bi bi-people"></i></a>
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