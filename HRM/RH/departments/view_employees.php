<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$dept_id = (int)$_GET['id'];

// Récupérer le nom du département
$stmt = $pdo->prepare('SELECT nom FROM departments WHERE id = ?');
$stmt->execute([$dept_id]);
$dept = $stmt->fetch();
if (!$dept) {
    header('Location: list.php');
    exit;
}

// Récupérer les employés du département
$stmt = $pdo->prepare('SELECT * FROM employees WHERE departement_id = ?');
$stmt->execute([$dept_id]);
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Employés du département</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../../custom.css" rel="stylesheet">
</head>
<body class="bg-light" id="body-root">
    <header class="navbar shadow-sm px-3 fixed-top align-items-center" style="z-index:1040; min-height:56px; height:56px; background:transparent !important; border:none; box-shadow:none;">
        <div class="ms-auto d-flex align-items-center">
            <button id="theme-toggle" class="btn btn-outline-primary me-2" title="Changer de mode">
                <span id="theme-icon" class="bi bi-moon"></span>
            </button>
        </div>
    </header>
    <div class="d-flex">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="flex-grow-1" style="margin-left:240px; padding-top:72px; min-height:100vh;">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold mb-0">Employés du département : <?= htmlspecialchars($dept['nom']) ?></h1>
                </div>
                <div class="card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if (count($employees) === 0): ?>
                            <p>Aucun employé dans ce département.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employees as $i => $emp): ?>
                                        <tr>
                                            <td><?= $i+1 ?></td>
                                            <td><?= htmlspecialchars($emp['nom']) ?></td>
                                            <td><?= htmlspecialchars($emp['prenom']) ?></td>
                                            <td><?= htmlspecialchars($emp['email']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-end mt-3">
                            <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour à la liste des départements</a>
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