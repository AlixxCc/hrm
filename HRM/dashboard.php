<?php
require 'includes/auth.php';
require_login();
require 'includes/db.php';

$prenom = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '';

// Statistiques RH
$nb_employes = $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
$nb_departements = $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn();
$nb_users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$nb_salaires = $pdo->query('SELECT COUNT(*) FROM salaries')->fetchColumn();

// Répartition employés par département
$departements = $pdo->query('SELECT d.nom, COUNT(e.id) as nb FROM departments d LEFT JOIN employees e ON e.departement_id = d.id GROUP BY d.id')->fetchAll();
$departement_labels = array_map(function($d) { return $d['nom']; }, $departements);
$departement_counts = array_map(function($d) { return (int)$d['nb']; }, $departements);

// Evolution du nombre d'employés sur 6 mois
$mois_labels = [];
$employes_par_mois = [];
for ($i = 5; $i >= 0; $i--) {
    $mois = date('Y-m', strtotime("-{$i} months"));
    $mois_labels[] = date('M Y', strtotime($mois.'-01'));
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE DATE_FORMAT(date_creation, "%Y-%m") <= ?');
    $stmt->execute([$mois]);
    $employes_par_mois[] = (int)$stmt->fetchColumn();
}

// Nouvelle requête pour récupérer les contrats
$stmtContracts = $pdo->prepare(
    'SELECT nom, prenom, contrat, date_fin_contrat
     FROM employees
     WHERE contrat IS NOT NULL AND contrat <> ""
       AND (DATE(date_fin_contrat) < CURDATE() OR DATE(date_fin_contrat) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
     ORDER BY date_fin_contrat ASC'
);
$stmtContracts->execute();
$contrats = $stmtContracts->fetchAll(PDO::FETCH_ASSOC);

// Séparer les contrats en expirés et à renouveler bientôt
$contratsExpired = [];
$contratsSoon = [];
foreach ($contrats as $c) {
    if (strtotime($c['date_fin_contrat']) < time()) {
        $contratsExpired[] = $c;
    } else {
        $contratsSoon[] = $c;
    }
}

// Dernières actions (5 derniers employés ajoutés)
$derniers_employes = $pdo->query('SELECT nom, prenom, date_creation FROM employees ORDER BY date_creation DESC LIMIT 5')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="custom.css" rel="stylesheet">
</head>
<body class="bg-light" id="body-root">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
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
            <div class="d-flex justify-content-between align-items-center" style="margin:16px 8px 20px 8px;">
                <h1 class="h3 fw-bold mb-0">Tableau de bord </h1>
            </div>
            <div class="card shadow-sm mb-4 animate-fade-in">
                <div class="card-body">
                    <h1 class="card-title h4 fs-4">Bienvenue sur le tableau de bord </h1>
                </div>
            </div>
            <?php if ($_SESSION['role'] === 'RH'): ?>
            <div class="row g-2 mb-4">
                <!-- <div class="col-md-6 col-lg-4 mb-4"> -->
                    <!-- <div class="card shadow-sm h-100 p-3">
                        <h6 class="mb-2">Alertes & Rappels</h6>

                        <?php if (count($contratsExpired) > 0): ?>
                            <div class="alert alert-danger py-2 mb-2">
                                <i class="bi bi-x-circle me-1"></i> <?= count($contratsExpired) ?> contrat(s) expiré(s)
                                <div class="mt-2 small">
                                    <?php foreach ($contratsExpired as $c): ?>
                                        <div><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?> <span class="text-muted"> (<?= date('d-m-Y', strtotime($c['date_fin_contrat'])) ?>)</span></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($contratsSoon) > 0): ?>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i> <?= count($contratsSoon) ?> contrat(s) à renouveler bientôt
                                <div class="mt-2 small">
                                    <?php foreach ($contratsSoon as $c): ?>
                                        <div><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?> <span class="text-muted"> (<?= date('d-m-Y', strtotime($c['date_fin_contrat'])) ?>)</span></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($contratsExpired) === 0 && count($contratsSoon) === 0): ?>
                            <div class="text-muted">Aucune alerte pour le moment.</div>
                        <?php endif; ?>
                    </div> -->
                <!-- </div> -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100 p-3">
                        <h6 class="mb-2">Dernières actions</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($derniers_employes as $emp): ?>
                                <li class="list-group-item small">
                                    <i class="bi bi-person me-1"></i> <?= htmlspecialchars($emp['nom'].' '.$emp['prenom']) ?> <span class="text-muted">(<?= date('d-m-Y', strtotime($emp['date_creation'])) ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm h-100 p-3">
                        <h6 class="mb-2">Accès rapide</h6>
                        <a href="RH/employees/add.php" class="btn btn-success w-100 mb-2"><i class="bi bi-person-plus me-1"></i>Ajouter un employé</a>
                        <a href="RH/departments/add.php" class="btn btn-primary w-100 mb-2"><i class="bi bi-plus-square me-1"></i>Créer un département</a>
                        <a href="RH/users/add.php" class="btn btn-info w-100 mb-2"><i class="bi bi-person-plus-fill me-1"></i>Inviter un utilisateur</a>
                        <a href="Comptable/salaries/list.php" class="btn btn-outline-primary w-100"><i class="bi bi-cash-stack me-1"></i>Accéder à la paie</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <!-- Bootstrap 5 JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const themeToggle = document.getElementById('theme-toggle');
    const bodyRoot = document.getElementById('body-root');
    const themeIcon = document.getElementById('theme-icon');
    const sidebar = document.getElementById('sidebar');
    // Initialisation du thème
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