<?php
if (!isset($_SESSION)) session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<nav id="sidebar" class="sidebar bg-white border-end pt-5 d-flex flex-column flex-shrink-0 p-3" style="width: 240px; height: 100vh; position:fixed; top:0; left:0; z-index:1030;">
    <div class="sidebar-logo text-center mb-4">
        <img src="/HRM/HRM/logo.png" alt="Logo HRM" width="130" height="auto" style="max-width:130px; height:auto;">
    </div>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="/HRM/HRM/dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
        </li>
        <?php if ($role === 'RH') : ?>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/RH/employees/list.php" class="nav-link<?php if(strpos($_SERVER['SCRIPT_NAME'], '/employees/') !== false) echo ' active'; ?>"><i class="bi bi-people me-2"></i>Employés</a>
            </li>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/RH/departments/list.php" class="nav-link<?php if(strpos($_SERVER['SCRIPT_NAME'], '/departments/') !== false) echo ' active'; ?>"><i class="bi bi-diagram-3 me-2"></i>Départements</a>
            </li>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/RH/users/list.php" class="nav-link<?php if(strpos($_SERVER['SCRIPT_NAME'], '/users/') !== false) echo ' active'; ?>"><i class="bi bi-person-badge me-2"></i>Utilisateurs</a>
            </li>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/Comptable/salaries/list.php" class="nav-link<?php if(strpos($_SERVER['SCRIPT_NAME'], '/salaries/') !== false) echo ' active'; ?>"><i class="bi bi-cash-stack me-2"></i>Salaires</a>
            </li>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/RH/conges/list.php" class="nav-link<//?php if(strpos($_SERVER['SCRIPT_NAME'], '/conges/') !== false) echo ' active'; ?>"><i class="bi bi-calendar-check me-2"></i>Congés</a>
            </li> 
        <?php elseif ($role === 'Comptable') : ?>
            <li class="nav-item mb-2">
                <a href="/HRM/HRM/Comptable/salaries/list.php" class="nav-link<?php if(strpos($_SERVER['SCRIPT_NAME'], '/salaries/') !== false) echo ' active'; ?>"><i class="bi bi-cash-stack me-2"></i>Salaires</a>
            </li>
        <?php endif; ?>
        <li class="nav-item mt-3">
            <a href="/HRM/HRM/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a>
        </li>
    </ul>
</nav> 