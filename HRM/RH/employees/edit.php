<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

$projets = ['Projet Alpha', 'Projet Beta', 'Projet Gamma', 'Aucun'];

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) {
    header('Location: list.php');
    exit;
}

$stmt = $pdo->query('SELECT * FROM departments');
$departments = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $email_professionnel = trim($_POST['email_professionnel'] ?? '');
    $telephone_whatsapp = trim($_POST['telephone_whatsapp'] ?? '');
    $telephone_local = trim($_POST['telephone_local'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $lieu_affectation = trim($_POST['lieu_affectation'] ?? '');
    $diplome = trim($_POST['diplome'] ?? '');
    $fonction = trim($_POST['fonction'] ?? '');
    $salaire_net = trim($_POST['salaire_net'] ?? '');
    $projet_affectation = $_POST['projet_affectation'] ?? 'Aucun';
    $departement_id = (int)($_POST['departement_id'] ?? 0);
    $contrat_path = $emp['contrat'];
    $date_debut_contrat = $_POST['date_debut_contrat'] ?? '';
    $date_fin_contrat = $_POST['date_fin_contrat'] ?? '';

    if (isset($_FILES['contrat']) && $_FILES['contrat']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/contrats/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['contrat']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'pdf') {
            $contrat_path = $upload_dir . uniqid('contrat_') . '.pdf';
            move_uploaded_file($_FILES['contrat']['tmp_name'], $contrat_path);
            $contrat_path = str_replace('../../', '', $contrat_path);
        } else {
            $message = '<div class="alert alert-danger">Le contrat doit être un fichier PDF.</div>';
        }
    }

    if ($nom && $prenom && $email && $departement_id && $salaire_net !== '') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE (email = ? OR email_professionnel = ?) AND id != ?');
        $stmt->execute([$email, $email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $message = '<div class="alert alert-danger">Cet email existe déjà pour un autre employé (comme email principal ou professionnel). Veuillez en choisir un autre.</div>';
        } else if ($email_professionnel) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE (email = ? OR email_professionnel = ?) AND id != ?');
            $stmt->execute([$email_professionnel, $email_professionnel, $id]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="alert alert-danger">Cet email professionnel existe déjà pour un autre employé (comme email principal ou professionnel). Veuillez en choisir un autre.</div>';
            } else {
                $monnaie = $_POST['monnaie'] ?? 'Fbu';
                $stmt = $pdo->prepare('UPDATE employees SET nom = ?, prenom = ?, email = ?, email_professionnel = ?, telephone_whatsapp = ?, telephone_local = ?, adresse = ?, lieu_affectation = ?, diplome = ?, fonction = ?, salaire_net = ?, monnaie = ?, projet_affectation = ?, contrat = ?, departement_id = ?, date_debut_contrat = ?, date_fin_contrat = ? WHERE id = ?');
                $stmt->execute([$nom, $prenom, $email, $email_professionnel, $telephone_whatsapp, $telephone_local, $adresse, $lieu_affectation, $diplome, $fonction, $salaire_net, $monnaie, $projet_affectation, $contrat_path, $departement_id, $date_debut_contrat, $date_fin_contrat, $id]);
                header('Location: list.php');
                exit;
            }
        } else {
            $monnaie = $_POST['monnaie'] ?? 'Fbu';
            $stmt = $pdo->prepare('UPDATE employees SET nom = ?, prenom = ?, email = ?, email_professionnel = ?, telephone_whatsapp = ?, telephone_local = ?, adresse = ?, lieu_affectation = ?, diplome = ?, fonction = ?, salaire_net = ?, monnaie = ?, projet_affectation = ?, contrat = ?, departement_id = ?, date_debut_contrat = ?, date_fin_contrat = ? WHERE id = ?');
            $stmt->execute([$nom, $prenom, $email, $email_professionnel, $telephone_whatsapp, $telephone_local, $adresse, $lieu_affectation, $diplome, $fonction, $salaire_net, $monnaie, $projet_affectation, $contrat_path, $departement_id, $date_debut_contrat, $date_fin_contrat, $id]);
            header('Location: list.php');
            exit;
        }
    } else if (!$message) {
        $message = '<div class="alert alert-danger">Les champs obligatoires sont requis.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un employé</title>
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
                <h1 class="h3 fw-bold mb-0">Modifier un employé</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <div class="form-card card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if ($message) echo $message; ?>
                        <form method="post" enctype="multipart/form-data" autocomplete="off">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom" id="nom" required value="<?= htmlspecialchars($emp['nom']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="prenom" id="prenom" required value="<?= htmlspecialchars($emp['prenom']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email" required value="<?= htmlspecialchars($emp['email']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email_professionnel" class="form-label">Email professionnel</label>
                                    <input type="email" class="form-control" name="email_professionnel" id="email_professionnel" value="<?= htmlspecialchars($emp['email_professionnel']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone_whatsapp" class="form-label">Téléphone WhatsApp</label>
                                    <input type="text" class="form-control" name="telephone_whatsapp" id="telephone_whatsapp" value="<?= htmlspecialchars($emp['telephone_whatsapp']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone_local" class="form-label">Téléphone local</label>
                                    <input type="text" class="form-control" name="telephone_local" id="telephone_local" value="<?= htmlspecialchars($emp['telephone_local']) ?>">
                                </div>
                                <div class="col-md-12">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" name="adresse" id="adresse" value="<?= htmlspecialchars($emp['adresse']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lieu_affectation" class="form-label">Lieu d'affectation</label>
                                    <input type="text" class="form-control" name="lieu_affectation" id="lieu_affectation" value="<?= htmlspecialchars($emp['lieu_affectation']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="diplome" class="form-label">Diplôme</label>
                                    <input type="text" class="form-control" name="diplome" id="diplome" value="<?= htmlspecialchars($emp['diplome']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <input type="text" class="form-control" name="fonction" id="fonction" value="<?= htmlspecialchars($emp['fonction'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="salaire_net" class="form-label">Salaire net <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" name="salaire_net" id="salaire_net" required value="<?= htmlspecialchars($emp['salaire_net']) ?>">
                                        <select class="form-select" name="monnaie" id="monnaie">
                                            <option value="Fbu" <?= $emp['monnaie'] === 'Fbu' ? 'selected' : '' ?>>Fbu</option>
                                            <option value="USD" <?= $emp['monnaie'] === 'USD' ? 'selected' : '' ?>>USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="projet_affectation" class="form-label">Projet d'affectation</label>
                                    <select class="form-select" name="projet_affectation" id="projet_affectation">
                                        <?php foreach ($projets as $projet): ?>
                                            <option value="<?= htmlspecialchars($projet) ?>" <?= $emp['projet_affectation'] === $projet ? 'selected' : '' ?>><?= htmlspecialchars($projet) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="departement_id" class="form-label">Département <span class="text-danger">*</span></label>
                                    <select class="form-select" name="departement_id" id="departement_id" required>
                                        <option value="">--Choisir--</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>" <?= $emp['departement_id'] == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="contrat" class="form-label">Contrat (PDF)</label>
                                    <input type="file" class="form-control" name="contrat" id="contrat" accept="application/pdf">
                                    <?php if ($emp['contrat']): ?>
                                        <a href="../../<?= htmlspecialchars($emp['contrat']) ?>" target="_blank" class="d-block mt-2">Voir le contrat actuel</a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_debut_contrat" class="form-label">Date de début du contrat</label>
                                    <input type="date" class="form-control" name="date_debut_contrat" id="date_debut_contrat" value="<?= htmlspecialchars($emp['date_debut_contrat'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_fin_contrat" class="form-label">Date de fin du contrat</label>
                                    <input type="date" class="form-control" name="date_fin_contrat" id="date_fin_contrat" value="<?= htmlspecialchars($emp['date_fin_contrat'] ?? '') ?>">
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