<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    if ($nom) {
        $stmt = $pdo->prepare('INSERT INTO departments (nom) VALUES (?)');
        $stmt->execute([$nom]);
        header('Location: list.php');
        exit;
    } else {
        $message = "<span style='color:red'>Le nom est requis.</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un département</title>
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
                <h1 class="h3 fw-bold mb-0">Ajouter un département</h1>
                <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour à la liste</a>
            </div>
            <div style="max-height:70vh; overflow-y:auto;">
                <?php if ($message) echo str_starts_with($message, '<') ? $message : '<div class="alert alert-danger">'.$message.'</div>'; ?>
                <form method="post" class="mt-4">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du département <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="nom" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-end">
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
    </main>
</body>
</html> 