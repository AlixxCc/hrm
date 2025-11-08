<?php
require '../../includes/auth.php';
require_role('RH');
require '../../includes/db.php';

// Récupérer toutes les demandes de congés avec infos employé
$stmt = $pdo->query('SELECT c.*, e.nom, e.prenom FROM conges c JOIN employees e ON c.employe_id = e.id ORDER BY c.date_demande DESC');
$conges = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des demandes de congés</title>
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
                <h1 class="h3 fw-bold mb-0">Demandes de congés</h1>
            </div>
            <div class="card shadow-sm animate-fade-in mt-4">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:70vh; overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employé</th>
                                    <th>Fonction</th>
                                    <th>Service</th>
                                    <th>Zone intervention</th>
                                    <!-- <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Numéro congé</th> -->
                                    <!-- <th>Début</th>
                                    <th>Fin</th> -->
                                    <!-- <th>Nature</th>
                                    <th>Motif</th> -->
                                    <th>Statut</th>
                                    <!--<th>Commentaire RH</th>-->
                                    <!-- <th>Date demande</th> -->
                                    <!--<th>Date traitement</th>-->
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $num = 1; foreach ($conges as $conge): ?>
                                <tr>
                                    <td><?= $num++ ?></td>
                                    <td><?= htmlspecialchars($conge['nom'].' '.$conge['prenom']) ?></td>
                                    <td><?= htmlspecialchars($conge['fonction']) ?></td>
                                    <td><?= htmlspecialchars($conge['service']) ?></td>
                                    <td><?= htmlspecialchars($conge['zone_intervention']) ?></td>
                                    <!-- <td><?= htmlspecialchars($conge['telephone']) ?></td>
                                    <td><?= htmlspecialchars($conge['email']) ?></td>
                                    <td><?= htmlspecialchars($conge['numero_conge']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($conge['date_debut']) ?></td>
                                    <td><?= htmlspecialchars($conge['date_fin']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($conge['nature']) ?></td>
                                    <td><?= htmlspecialchars($conge['motif']) ?></td> -->
                                    <td><?= htmlspecialchars($conge['statut']) ?></td>
                                    <!--<td><//?= htmlspecialchars($conge['commentaire_rh']) ?></td>-->
                                    <!-- <td><?= htmlspecialchars($conge['date_demande']) ?></td> -->
                                    <!--<td><//?= htmlspecialchars($conge['date_traitement']) ?></td>-->
                                    <td>
                                        <a href="view.php?id=<?= $conge['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="Consulter"><i class="bi bi-eye"></i></a>
                                        <a href="delete.php?id=<?= $conge['id'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer la demande de congé de <?= htmlspecialchars($conge['prenom'] . ' ' . $conge['nom']) ?> ?');"><i class="bi bi-trash"></i></a>
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