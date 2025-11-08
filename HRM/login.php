<?php
session_start();
require 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="custom.css" rel="stylesheet">
    <style>
      html, body { height: 100%; min-height: 100vh; }
      body {
        font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
        background: #f5f7fa;
        min-height: 100vh;
      }
      .login-split {
        display: flex;
        min-height: 100vh;
        position: relative;
      }
      .login-left {
        flex: 0 0 50%;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0 2.5rem;
      }
      .login-form-card {
        width: 100%;
        max-width: 410px;
        border-radius: 1.2rem;
        box-shadow: 0 4px 32px rgba(26,35,126,0.13);
        padding: 2.2rem 2rem 2rem 2rem;
        background: rgba(255,255,255,0.97);
        margin: 0 auto;
        text-align: center;
      }
      .login-logo {
        margin-bottom: 1.2rem;
      }
      .login-logo img {
        max-width: 120px;
        width: 120px;
        height: auto;
        /*box-shadow: 0 4px 24px rgba(26,35,126,0.18);
        /*border-radius: 0.7rem;*/
      }
      .login-title {
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 1.2rem;
        color: #233876;
      }
      .login-subtitle {
        color: #888;
        font-size: 1.1rem;
        margin-bottom: 2.2rem;
      }
      .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
      }
      .form-control {
        margin-bottom: 1.1rem;
      }
      .btn-primary {
        width: 100%;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.7rem;
        border-radius: 0.7rem;
        background: #233876;
        border: none;
        color: #fff;
        transition: background 0.2s;
      }
      .btn-primary:hover, .btn-primary:focus {
        background: #3551a3;
        color: #fff;
      }
      .login-error {
        color: #E53935;
        margin-top: 0.7rem;
        font-weight: 500;
      }
      .login-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.97rem;
        margin-bottom: 1.1rem;
      }
      .login-links a {
        color: #233876;
        text-decoration: none;
        font-weight: 500;
      }
      .login-links a:hover {
        text-decoration: underline;
      }
      /* Right side */
      .login-right {
        flex: 0 0 50%;
        position: relative;
        background: #1a237e;
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
        overflow: hidden;
      }
      .login-right-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.6) blur(0.5px);
        z-index: 1;
        transition: transform 1.2s cubic-bezier(.22,.61,.36,1), filter 0.5s;
      }
      .login-right:hover .login-right-bg {
        transform: scale(1.06);
        filter: brightness(0.7) blur(1px);
      }
      .login-right-overlay {
        position: absolute;
        inset: 0;
        background: rgba(26,35,126,0.65);
        z-index: 2;
      }
      .login-right-content {
        position: relative;
        z-index: 3;
        color: #fff;
        padding: 0 3vw 5vh 3vw;
        width: 100%;
        max-width: 600px;
        margin-left: auto;
        margin-bottom: 4vh;
        text-align: right;
        opacity: 0;
        transform: translateY(40px);
        animation: fadeInUp 1.2s 0.3s cubic-bezier(.22,.61,.36,1) forwards;
      }
      @keyframes fadeInUp {
        to {
          opacity: 1;
          transform: none;
        }
      }
      .login-right-content h2 {
        font-size: 2.1rem;
        font-weight: 700;
        margin-bottom: 1.1rem;
        line-height: 1.2;
      }
      .login-right-content .highlight {
        color: #FFC107;
        font-weight: 700;
      }
      .bienvenue{
        color: #fff;
      }
      .login-logo-on-image {
        position: absolute;
        top: 40px;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: center;
        z-index: 10;
      }
      .login-logo-on-image img {
        max-width: 120px;
        width: 120px;
        height: auto;
        box-shadow: 0 4px 24px rgba(26,35,126,0.28);
        border-radius: 0.7rem;
        background: rgba(255,255,255,0.85);
        padding: 0.5rem 1.2rem;
        border: 2px solid #fff;
      }
      .login-logo-above-form {
        opacity: 0;
        transform: translateY(40px);
        animation: fadeInUp 1.2s 0.2s cubic-bezier(.22,.61,.36,1) forwards;
        width: 100%;
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
      }
      .login-form-card.animate-fade-in {
        opacity: 0;
        transform: translateY(40px);
        animation: fadeInUp 1.2s 0.35s cubic-bezier(.22,.61,.36,1) forwards;
      }
      .login-logo-above-form img {
        max-width: 150px;
        width: 150px;
        height: auto;
      }
      .login-right-content-glass {
        position: relative;
        z-index: 3;
        width: 100%;
        max-width: 600px;
        margin-left: auto;
        margin-bottom: 4vh;
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        padding: 0 3vw 5vh 3vw;
      }
      .glass-panel {
        background: rgba(255,255,255,0.13);
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18);
        backdrop-filter: blur(12px) saturate(160%);
        -webkit-backdrop-filter: blur(12px) saturate(160%);
        border: 1.5px solid rgba(255,255,255,0.22);
        padding: 2.2rem 2.2rem 1.5rem 2.2rem;
        width: 100%;
        max-width: 520px;
        color: #fff;
        text-align: right;
        animation: fadeInUp 1.2s 0.3s cubic-bezier(.22,.61,.36,1) forwards;
        opacity: 0;
        transform: translateY(40px);
      }
      .glass-panel h2 {
        margin: 0;
        font-size: 2.1rem;
        font-weight: 700;
        line-height: 1.2;
      }
      .glass-panel .highlight {
        color: #FFC107;
        font-weight: 700;
      }
      .glass-panel .bienvenue {
        color: #fff;
      }
      .login-right-content-glass .glass-panel {
        opacity: 0;
        transform: translateY(40px);
        animation: fadeInUp 1.2s 0.3s cubic-bezier(.22,.61,.36,1) forwards;
      }
      .particles {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 3;
      }
      .particle {
        position: absolute;
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.18);
        border-radius: 50%;
        filter: blur(1.5px);
        opacity: 0.7;
        animation: floatParticle 7s linear infinite alternate;
      }
      .particle:nth-child(2) { width: 22px; height: 22px; background: rgba(255,255,255,0.13); }
      .particle:nth-child(3) { width: 38px; height: 38px; background: rgba(255,255,255,0.22); }
      .particle:nth-child(4) { width: 18px; height: 18px; background: rgba(255,255,255,0.16); }
      .particle:nth-child(5) { width: 28px; height: 28px; background: rgba(255,255,255,0.19); }
      .particle:nth-child(6) { width: 24px; height: 24px; background: rgba(255,255,255,0.11); }
      .particle:nth-child(7) { width: 34px; height: 34px; background: rgba(255,255,255,0.15); }
      @keyframes floatParticle {
        0%   { transform: translateY(0) scale(1); opacity: 0.7; }
        50%  { transform: translateY(-30px) scale(1.12); opacity: 0.9; }
        100% { transform: translateY(0) scale(1); opacity: 0.7; }
      }
      .vertical-divider {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        height: 100%;
        width: 16px;
        background: rgba(35,56,118,0.95);
        border-radius: 12px;
        border: 2px solid #fff;
        z-index: 100;
        box-shadow: 0 0 18px 0 #23387688;
        pointer-events: none;
      }
      @media (max-width: 900px) {
        .login-split { flex-direction: column; }
        .login-right, .login-left { flex: unset; min-width: 100vw; }
        .login-right { min-height: 260px; height: 38vh; }
        .login-left { min-height: 62vh; }
        .login-right-content { text-align: left; padding: 2vw 2vw 2vw 2vw; }
        .vertical-divider { display: none; }
      }
      @media (max-width: 600px) {
        .login-split { flex-direction: column; }
        .login-left, .login-right { flex: unset; min-width: 100vw; }
        .login-form-card { box-shadow: none; padding: 1.2rem 0.5rem; }
        .login-right-content { font-size: 1.1rem; padding: 1vw 1vw 2vw 1vw; }
      }
    </style>
</head>
<body id="body-root">
  <div class="login-split">
    <div class="vertical-divider"></div>
    <div class="login-left">
      <div class="login-logo login-logo-above-form">
        <img src="logo.png" alt="Logo HRM">
      </div>
      <div class="login-form-card animate-fade-in">
        <div class="login-title">Connexion</div>
        <div class="login-subtitle">Connectez-vous à votre compte.</div>
        <form method="post" autocomplete="on">
          <div class="mb-3 text-start">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="Adresse e-mail" required autofocus>
          </div>
          <div class="mb-3 text-start">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" name="mot_de_passe" id="mot_de_passe" placeholder="Mot de passe" required>
          </div>
          <div class="login-links mb-3">
            <!--<div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="rememberMe" disabled>
              <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
            </div>-->
            <!-- <a href="#" tabindex="-1">Mot de passe oublié&nbsp;?</a> -->
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-2"></i>Connexion</button>
          <?php if (!empty($error)): ?><div class="login-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        </form>
      </div>
    </div>
    <div class="login-right">
      <img src="image.jpg" alt="Illustration RH" class="login-right-bg">
      <div class="login-right-overlay">
        <div class="particles">
          <span class="particle" style="top:10%;left:15%;animation-delay:0s;"></span>
          <span class="particle" style="top:30%;left:70%;animation-delay:1.2s;"></span>
          <span class="particle" style="top:60%;left:40%;animation-delay:2.1s;"></span>
          <span class="particle" style="top:80%;left:80%;animation-delay:0.7s;"></span>
          <span class="particle" style="top:50%;left:20%;animation-delay:1.7s;"></span>
          <span class="particle" style="top:20%;left:85%;animation-delay:2.7s;"></span>
          <span class="particle" style="top:75%;left:60%;animation-delay:0.4s;"></span>
        </div>
      </div>
      <div class="login-right-content-glass">
        <div class="glass-panel">
          <h2><span class="bienvenue">Bienvenue sur le logiciel de </span> <span class="highlight">Gestion du personnel.</span> </h2>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Mode sombre auto si déjà choisi
    const bodyRoot = document.getElementById('body-root');
    if (localStorage.getItem('theme') === 'dark') {
      bodyRoot.classList.remove('bg-light');
      bodyRoot.classList.add('bg-dark', 'text-light');
    }
  </script>
</body>
</html> 