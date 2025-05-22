<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - salamagnon.fr</title>
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
            <a href="#" data-page="projets">(WIP) Projets</a>
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
