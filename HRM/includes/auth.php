<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header('Location: dashboard.php');
        exit;
    }
}

function require_roles($roles = []) {
    require_login();
    if (!in_array($_SESSION['role'], $roles)) {
        header('Location: dashboard.php');
        exit;
    }
}
?> 