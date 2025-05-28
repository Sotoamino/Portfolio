<?php
// Sécurité : Ne jamais afficher les erreurs en production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Sécurité session
session_start();

// Headers HTTP pour renforcer la sécurité côté client
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=()");
header("Strict-Transport-Security: max-age=63072000; includeSubDomains; preload"); // Si HTTPS uniquement

// Vérification d'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Expiration automatique de la session (15 minutes d'inactivité)
$maxInactivity = 900;
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $maxInactivity) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Regénérer l'ID de session périodiquement (toutes les 5 minutes)
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 300) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSP (Content Security Policy) minimal -->

    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <header class="navbar">
        <h1>Back Office</h1>
        <nav>
            <a href="#" data-page="dashboard">🏠 Accueil</a>
            <a href="#" data-page="informations">🙋 Informations</a>
            <a href="#" data-page="competences">🛠️ Compétences</a>
            <a href="#" data-page="langues">🌐 Langues</a>
            <a href="#" data-page="experiences">📅 Expériences</a>
            <a href="#" data-page="formations">💼 Formations</a>
            <a href="#" data-page="projets">🗂️ Projets</a>
            <a href="logout.php">🚪 Déconnexion</a>
        </nav>
    </header>

    <main class="content" id="main-content">
        <p>Comming soon</p>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="assets/js/admin.js"></script>

</body>
</html>
