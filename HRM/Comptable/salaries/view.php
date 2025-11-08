<?php
require '../../includes/auth.php';
require_roles(['RH', 'Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer le salaire avec info employé et département
$stmt = $pdo->prepare('SELECT s.*, e.nom AS emp_nom, e.prenom AS emp_prenom, d.nom AS departement_nom FROM salaries s LEFT JOIN employees e ON s.employe_id = e.id LEFT JOIN departments d ON e.departement_id = d.id WHERE s.id = ?');
$stmt->execute([$id]);
$sal = $stmt->fetch();
if (!$sal) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du salaire</title>
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
                <h1 class="h3 fw-bold mb-0">Détails du salaire</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm animate-fade-in mt-4">
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item"><strong>Employé :</strong> <?= htmlspecialchars(($sal['emp_nom'] ?? '') . ' ' . ($sal['emp_prenom'] ?? '')) ?></li>
                                <li class="list-group-item"><strong>Département :</strong> <?= htmlspecialchars($sal['departement_nom'] ?? '') ?></li>
                                <li class="list-group-item"><strong>Salaire brut :</strong> <?= htmlspecialchars($sal['salaire_brut'] ?? '') ?> <?= htmlspecialchars($sal['monnaie'] ?? '') ?></li>
                                <li class="list-group-item"><strong>Salaire net :</strong> <?= htmlspecialchars($sal['salaire_net'] ?? '') ?> <?= htmlspecialchars($sal['monnaie'] ?? '') ?></li>
                                <li class="list-group-item"><strong>Date dernier paiement :</strong> <?= htmlspecialchars($sal['date_dernier_paiement'] ?? '') ?></li>
                            </ul>
                            <div class="d-flex justify-content-end">
                                <a href="edit.php?id=<?= $sal['id'] ?>" class="btn btn-outline-warning me-2"><i class="bi bi-pencil me-2"></i>Modifier</a>
                                <a href="pay.php?id=<?= $sal['id'] ?>" class="btn btn-outline-success me-2"><i class="bi bi-cash-coin me-2"></i>Paiement</a>
                                <a href="history.php?id=<?= $sal['id'] ?>" class="btn btn-outline-secondary me-2"><i class="bi bi-clock-history me-2"></i>Historique</a>
                                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour</a>
                            </div>
                        </div>
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