<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../tools/sqlconnect.php';

$nom = $_POST['nom'] ?? '';
$niveau = $_POST['niveau'] ?? '';

if (!$nom || !is_numeric($niveau)) {
    echo json_encode(['success' => false, 'message' => 'Nom ou niveau invalide.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO competences (nom, niveau) VALUES (:nom, :niveau)");
    $stmt->execute(['nom' => $nom, 'niveau' => $niveau]);
    $id = $pdo->lastInsertId();


    echo json_encode(['success' => true, 'message' => 'Compétence ajoutée avec succès.', 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
}
