<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = (int)$_GET['id'];

// Récupérer l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: list.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'RH';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis.";
    }
    if (empty($email)) {
        $errors[] = "L'email est requis.";
    }
    if (empty($role)) {
        $errors[] = "Le rôle est requis.";
    }

    if (empty($errors)) {
        // Vérifier si l'email existe déjà chez un autre utilisateur
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "<span style='color:red'>Cet email existe déjà. Veuillez en choisir un autre.</span>";
        } else {
            if ($mot_de_passe) {
                $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, role = ? WHERE id = ?');
                $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash, $role, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET nom = ?, prenom = ?, email = ?, role = ? WHERE id = ?');
                $stmt->execute([$nom, $prenom, $email, $role, $id]);
            }
            header('Location: list.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
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
                <h1 class="h3 fw-bold mb-0">Modifier un utilisateur</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <div class="form-card card shadow-sm animate-fade-in">
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $err): ?>
                                    <div><?= htmlspecialchars($err) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Choisir...</option>
                                    <option value="RH" <?= (isset($user['role']) && $user['role'] === 'RH') ? 'selected' : '' ?>>RH</option>
                                    <option value="Comptable" <?= (isset($user['role']) && $user['role'] === 'Comptable') ? 'selected' : '' ?>>Comptable</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Enregistrer</button>
                                <a href="list.php" class="btn btn-outline-secondary ms-2"><i class="bi bi-arrow-left me-2"></i>Annuler</a>
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