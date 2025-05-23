<?php
// Paramètres de connexion à la base de données
require_once 'options.php';  // Inclure le fichier options.php pour récupérer les paramètres de connexion
$charset = 'utf8mb4';  // Jeu de caractères pour éviter les problèmes d'encodage
global $pdo;
// DSN (Data Source Name) pour la connexion
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options pour la connexion PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Gestion des erreurs avec exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Mode de récupération des résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,  // Désactiver la simulation de requêtes préparées
];

// Tentative de connexion à la base de données
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la connexion échoue, afficher l'erreur
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;  // Arrêter l'exécution du script si la connexion échoue
}
?>
