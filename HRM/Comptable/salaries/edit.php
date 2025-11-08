<?php
require '../../includes/auth.php';
require_roles(['Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer le salaire
$stmt = $pdo->prepare('SELECT * FROM salaries WHERE id = ?');
$stmt->execute([$id]);
$sal = $stmt->fetch();
if (!$sal) {
    header('Location: list.php');
    exit;
}

// Récupérer la liste des employés avec salaire_net et monnaie
$stmt = $pdo->query('SELECT id, nom, prenom, salaire_net, monnaie FROM employees');
$employees = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employe_id = (int)($_POST['employe_id'] ?? 0);
    $salaire_net = floatval($_POST['salaire_net'] ?? 0);
    // Calcul automatique du brut (exemple : 10% de retenue)
    $salaire_brut = $salaire_net / 0.9;
    // Récupérer la monnaie de l'employé sélectionné
    $monnaie = '';
    foreach ($employees as $emp) {
        if ($emp['id'] == $employe_id) {
            $monnaie = $emp['monnaie'];
            break;
        }
    }
    if ($employe_id && $salaire_brut > 0) {
        $stmt = $pdo->prepare('UPDATE salaries SET employe_id = ?, salaire_brut = ?, salaire_net = ?, monnaie = ? WHERE id = ?');
        $stmt->execute([$employe_id, $salaire_brut, $salaire_net, $monnaie, $id]);
        header('Location: list.php');
        exit;
    } else {
        $message = "<span style='color:red'>Tous les champs obligatoires sont requis.</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un salaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../../custom.css" rel="stylesheet">
    <script>
    var employes = <?php echo json_encode($employees); ?>;
    function remplirSalaireNetEtMonnaie() {
        var select = document.getElementById('employe_id');
        var id = select.value;
        var salaireNet = '';
        var monnaie = '';
        for (var i = 0; i < employes.length; i++) {
            if (employes[i].id == id) {
                salaireNet = employes[i].salaire_net;
                monnaie = employes[i].monnaie;
                break;
            }
        }
        document.getElementById('salaire_net').value = salaireNet;
        document.getElementById('monnaie_net').textContent = monnaie ? monnaie : '';
        document.getElementById('monnaie_brut').textContent = monnaie ? monnaie : '';
        // Calculer le brut automatiquement
        if (salaireNet) {
            var brut = parseFloat(salaireNet) / 0.9;
            document.getElementById('salaire_brut').value = brut.toFixed(2);
        } else {
            document.getElementById('salaire_brut').value = '';
        }
    }
    </script>
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
                <h1 class="h3 fw-bold mb-0">Modifier un salaire</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <div class="form-card card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if ($message) echo str_starts_with($message, '<') ? $message : '<div class=\"alert alert-danger\">'.$message.'</div>'; ?>
                        <form method="post" class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label for="employe_id" class="form-label">Employé <span class="text-danger">*</span></label>
                                <select name="employe_id" id="employe_id" class="form-select" required onchange="remplirSalaireNetEtMonnaie()">
                                    <option value="">--Choisir--</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp['id'] ?>" <?= $sal['employe_id'] == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['nom'] . ' ' . $emp['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="salaire_brut" class="form-label">Salaire brut</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="salaire_brut" id="salaire_brut" class="form-control" value="<?= htmlspecialchars($sal['salaire_brut']) ?>" readonly>
                                    <span class="input-group-text" id="monnaie_brut"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="salaire_net" class="form-label">Salaire net</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="salaire_net" id="salaire_net" class="form-control" value="<?= htmlspecialchars($sal['salaire_net']) ?>" readonly>
                                    <span class="input-group-text" id="monnaie_net"></span>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer
                                </button>
                                <a href="list.php" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-arrow-left me-2"></i>Annuler
                                </a>
                            </div>
                        </form>
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