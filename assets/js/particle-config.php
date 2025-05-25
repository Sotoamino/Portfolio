<?php
require '../../tools/sqlconnect.php';

// Vérification et ajout de la colonne particle_config si nécessaire
$columnCheck = $pdo->query("SHOW COLUMNS FROM settings LIKE 'particle_config'")->fetch(PDO::FETCH_ASSOC);
if (!$columnCheck) {
    $pdo->exec("ALTER TABLE settings ADD COLUMN particle_config VARCHAR(255) DEFAULT 'default.json'");
    $pdo->exec("UPDATE settings SET particle_config = 'default.json' WHERE id = 1");
}

// Récupérer la valeur de particle_config pour l'ID 1
$stmt = $pdo->prepare("SELECT particle_config FROM settings WHERE id = 1 LIMIT 1");
$stmt->execute();
$fileName = $stmt->fetchColumn();

header('Content-Type: application/json');

if ($fileName) {
    echo json_encode(['file' => $fileName]);
} else {
    echo json_encode(['error' => "Nom de fichier introuvable."]);
}
