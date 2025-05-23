<?php
// Démarrer la session
session_start();

// Supprimer toutes les variables de session
$_SESSION = [];

// Si tu veux détruire complètement la session, supprime aussi le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion après la déconnexion
header("Location: login.php");
exit;
