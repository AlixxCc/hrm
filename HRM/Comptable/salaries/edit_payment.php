<?php
require '../../includes/auth.php';
require_roles(['RH', 'Comptable']);
require '../../includes/db.php';

if (!isset($_GET['id']) || !isset($_GET['salaire_id'])) {
    header('Location: list.php');
    exit;
}
$payment_id = (int)$_GET['id'];
$salaire_id = (int)$_GET['salaire_id'];

// Liste des mois
$mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

// Ajout de la génération d'années dynamiques
$annee_courante = date('Y');
$annees = range($annee_courante - 5, $annee_courante + 2);

// Récupérer le paiement
$stmt = $pdo->prepare('SELECT * FROM salary_payments WHERE id = ?');
$stmt->execute([$payment_id]);
$pay = $stmt->fetch();
if (!$pay) {
    header('Location: history.php?id=' . $salaire_id);
    exit;
}

// Extraction mois/année de la période si possible
$mois_de = '';
$annee_de = '';
$mois_a = '';
$annee_a = '';
if (preg_match('/De ([^ ]+) (\d{4}) à ([^ ]+) (\d{4})/u', $pay['periode'], $matches)) {
    $mois_de = $matches[1];
    $annee_de = $matches[2];
    $mois_a = $matches[3];
    $annee_a = $matches[4];
} elseif (preg_match('/De ([^ ]+) à ([^ ]+)/u', $pay['periode'], $matches)) {
    $mois_de = $matches[1];
    $mois_a = $matches[2];
}

// Récupérer le salaire net de la fiche salaire liée
$stmt = $pdo->prepare('SELECT salaire_net FROM salaries WHERE id = ?');
$stmt->execute([$salaire_id]);
$salaire = $stmt->fetch();
$salaire_net = $salaire ? floatval($salaire['salaire_net']) : 0;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_paiement = $_POST['date_paiement'] ?? '';
    $methode_paiement = trim($_POST['methode_paiement'] ?? '');
    $mois_de = $_POST['mois_de'] ?? '';
    $annee_de = $_POST['annee_de'] ?? '';
    $mois_a = $_POST['mois_a'] ?? '';
    $annee_a = $_POST['annee_a'] ?? '';
    $montant_net = floatval($_POST['montant_net'] ?? 0);
    $periode = ($mois_de && $annee_de && $mois_a && $annee_a) ? ("De $mois_de $annee_de à $mois_a $annee_a") : '';
    // Calcul du montant total prenant en compte les années
    $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $idxDe = array_search($mois_de, $mois);
    $idxA = array_search($mois_a, $mois);
    $nbMois = 0;
    if ($idxDe !== false && $idxA !== false && $annee_de > 0 && $annee_a > 0 && ($annee_a > $annee_de || ($annee_a == $annee_de && $idxA >= $idxDe))) {
        $nbMois = ($annee_a - $annee_de) * 12 + ($idxA - $idxDe) + 1;
    }
    $montant_total = $nbMois * $montant_net;
    if ($date_paiement && $methode_paiement && $periode && $montant_total > 0 && $montant_net > 0) {
        $stmt = $pdo->prepare('UPDATE salary_payments SET date_paiement = ?, methode_paiement = ?, periode = ?, montant_total = ?, montant_net = ? WHERE id = ?');
        $stmt->execute([$date_paiement, $methode_paiement, $periode, $montant_total, $montant_net, $payment_id]);
        header('Location: history.php?id=' . $salaire_id);
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
    <title>Modifier un paiement</title>
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
        var montantNet = parseFloat(document.getElementById('montant_net').value) || 0;
        var idxDe = mois.indexOf(moisDe);
        var idxA = mois.indexOf(moisA);
        var nbMois = 0;
        if (idxDe !== -1 && idxA !== -1 && anneeDe > 0 && anneeA > 0 && (anneeA > anneeDe || (anneeA === anneeDe && idxA >= idxDe))) {
            nbMois = (anneeA - anneeDe) * 12 + (idxA - idxDe) + 1;
        }
        var total = nbMois * montantNet;
        document.getElementById('montant_total').value = total.toFixed(2);
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
                <h1 class="h3 fw-bold mb-0">Modifier un paiement</h1>
                <a href="history.php?id=<?= $salaire_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à l'historique</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <div class="form-card card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if ($message) echo str_starts_with($message, '<') ? $message : '<div class=\"alert alert-danger\">'.$message.'</div>'; ?>
                        <form method="post" class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="date_paiement" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                <input type="date" name="date_paiement" id="date_paiement" class="form-control" value="<?= htmlspecialchars($pay['date_paiement'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Période de paiement <span class="text-danger">*</span></label>
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <select name="mois_de" id="mois_de" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">De...</option>
                                            <?php foreach ($mois as $m): ?>
                                                <option value="<?= $m ?>" <?= $mois_de === $m ? 'selected' : '' ?>><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <select name="annee_de" id="annee_de" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">Année...</option>
                                            <?php foreach ($annees as $a): ?>
                                                <option value="<?= $a ?>" <?= $annee_de == $a ? 'selected' : '' ?>><?= $a ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">à</div>
                                    <div class="col-auto">
                                        <select name="mois_a" id="mois_a" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">À...</option>
                                            <?php foreach ($mois as $m): ?>
                                                <option value="<?= $m ?>" <?= $mois_a === $m ? 'selected' : '' ?>><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <select name="annee_a" id="annee_a" class="form-select" required onchange="calculerMontantTotal()">
                                            <option value="">Année...</option>
                                            <?php foreach ($annees as $a): ?>
                                                <option value="<?= $a ?>" <?= $annee_a == $a ? 'selected' : '' ?>><?= $a ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="montant_net" class="form-label">Montant net <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="montant_net" id="montant_net" class="form-control" value="<?= htmlspecialchars($pay['montant_net'] ?? $salaire_net) ?>" required oninput="calculerMontantTotal()" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="montant_total" class="form-label">Montant total <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="montant_total" id="montant_total" class="form-control" value="<?= htmlspecialchars($pay['montant_total'] ?? '') ?>" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="methode_paiement" class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                                <select name="methode_paiement" id="methode_paiement" class="form-select" required>
                                    <option value="virement" <?= ($pay['methode_paiement'] ?? '') === 'virement' ? 'selected' : '' ?>>Virement</option>
                                    <option value="chèque" <?= ($pay['methode_paiement'] ?? '') === 'chèque' ? 'selected' : '' ?>>Chèque</option>
                                    <option value="cash" <?= ($pay['methode_paiement'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer
                                </button>
                                <a href="history.php?id=<?= $salaire_id ?>" class="btn btn-outline-secondary ms-2">
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