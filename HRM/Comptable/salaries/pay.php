<?php
require '../../includes/auth.php';
require_roles(['Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Liste des mois
$mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

// Ajout de la génération d'années dynamiques
$annee_courante = date('Y');
$annees = range($annee_courante - 5, $annee_courante + 2);

// Récupérer le salaire avec la monnaie depuis la table salaries
$stmt = $pdo->prepare('SELECT * FROM salaries WHERE id = ?');
$stmt->execute([$id]);
$sal = $stmt->fetch();
if (!$sal) {
    header('Location: list.php');
    exit;
}
$monnaie = isset($sal['monnaie']) ? $sal['monnaie'] : '';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_paiement = $_POST['date_paiement'] ?? date('Y-m-d');
    $montant_net = floatval($_POST['montant_net'] ?? 0);
    $methode_paiement = trim($_POST['methode_paiement'] ?? '');
    $mois_de = $_POST['mois_de'] ?? '';
    $annee_de = $_POST['annee_de'] ?? '';
    $mois_a = $_POST['mois_a'] ?? '';
    $annee_a = $_POST['annee_a'] ?? '';
    $periode = ($mois_de && $annee_de && $mois_a && $annee_a) ? ("De $mois_de $annee_de à $mois_a $annee_a") : '';
    $montant_total = floatval($_POST['montant_total'] ?? 0);
    if ($date_paiement && $montant_net > 0 && $methode_paiement && $periode && $montant_total > 0) {
        $stmt = $pdo->prepare('INSERT INTO salary_payments (salaire_id, date_paiement, montant_net, methode_paiement, periode, montant_total) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $date_paiement, $montant_net, $methode_paiement, $periode, $montant_total]);
        $stmt = $pdo->prepare('UPDATE salaries SET date_dernier_paiement = ?, methode_paiement = ? WHERE id = ?');
        $stmt->execute([$date_paiement, $methode_paiement, $id]);
        header('Location: history.php?id=' . $id);
        exit;
    } else {
        $message = "<span style='color:red'>Tous les champs sont requis.</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement de salaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../../custom.css" rel="stylesheet">
    <script>
    function calculerMontantTotal() {
        var mois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        var moisDe = document.getElementById('mois_de').value;
        var moisA = document.getElementById('mois_a').value;
        var anneeDe = parseInt(document.getElementById('annee_de').value) || 0;
        var anneeA = parseInt(document.getElementById('annee_a').value) || 0;
        var salaireNet = parseFloat(document.getElementById('montant_net').value) || 0;
        var idxDe = mois.indexOf(moisDe);
        var idxA = mois.indexOf(moisA);
        var nbMois = 0;
        if (idxDe !== -1 && idxA !== -1 && anneeDe > 0 && anneeA > 0 && (anneeA > anneeDe || (anneeA === anneeDe && idxA >= idxDe))) {
            nbMois = (anneeA - anneeDe) * 12 + (idxA - idxDe) + 1;
        }
        var total = nbMois * salaireNet;
        document.getElementById('montant_total').value = total.toFixed(2);
    }
    function toggleBanqueField() {
        var methode = document.getElementById('methode_paiement').value;
        var banqueDiv = document.getElementById('banque_field');
        var banqueInput = document.getElementById('nom_banque');
        if (methode === 'virement') {
            banqueDiv.style.display = '';
            banqueInput.required = true;
        } else {
            banqueDiv.style.display = 'none';
            banqueInput.required = false;
            banqueInput.value = '';
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
                <h1 class="h3 fw-bold mb-0">Paiement de salaire</h1>
                <a href="list.php?id=<?= $id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <div class="form-card card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if ($message) echo str_starts_with($message, '<') ? $message : '<div class=\"alert alert-danger\">'.$message.'</div>'; ?>
                        <form method="post" class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="date_paiement" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                <input type="date" name="date_paiement" id="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Période de paiement <span class="text-danger">*</span></label>
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <select name="mois_de" id="mois_de" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">De...</option>
                                            <?php foreach ($mois as $m): ?>
                                                <option value="<?= $m ?>"><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <select name="annee_de" id="annee_de" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">Année...</option>
                                            <?php foreach ($annees as $a): ?>
                                                <option value="<?= $a ?>"><?= $a ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">à</div>
                                    <div class="col-auto">
                                        <select name="mois_a" id="mois_a" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">À...</option>
                                            <?php foreach ($mois as $m): ?>
                                                <option value="<?= $m ?>"><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <select name="annee_a" id="annee_a" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">Année...</option>
                                            <?php foreach ($annees as $a): ?>
                                                <option value="<?= $a ?>"><?= $a ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="montant_net" class="form-label">Montant net (<?= htmlspecialchars($monnaie) ?>) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="montant_net" id="montant_net" class="form-control" value="<?= htmlspecialchars($sal['salaire_net'] ?? '') ?>" oninput="calculerMontantTotal()" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="montant_total" class="form-label">Montant total (<?= htmlspecialchars($monnaie) ?>) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="montant_total" id="montant_total" class="form-control" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="methode_paiement" class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                                <select name="methode_paiement" id="methode_paiement" class="form-select" required>
                                    <option value="virement" <?= (!isset($sal['methode_paiement']) && $sal['methode_paiement'] === 'virement') ? 'selected' : '' ?>>Virement</option>
                                    <option value="chèque" <?= (isset($sal['methode_paiement']) && $sal['methode_paiement'] === 'chèque') ? 'selected' : '' ?>>Chèque</option>
                                    <option value="cash" <?= (isset($sal['methode_paiement']) && $sal['methode_paiement'] === 'cash') ? 'selected' : '' ?>>Cash</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le paiement
                                </button>
                                <a href="list.php?id=<?= $id ?>" class="btn btn-outline-secondary ms-2">
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