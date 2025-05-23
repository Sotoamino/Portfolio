<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
require_once '../../../tools/sqlconnect.php';

$data = $_POST;

$titre = trim($data['titre'] ?? '');
$description = trim($data['description'] ?? '');
$link = trim($data['link'] ?? '');

if ($titre === '' || $description === '') {
    echo json_encode(['success' => false, 'message' => 'Titre et description obligatoires']);
    exit;
}

// Trouver l'ordre max actuel
$maxOrdre = $pdo->query("SELECT MAX(ordre) FROM projets")->fetchColumn();
$maxOrdre = $maxOrdre !== false ? (int)$maxOrdre + 1 : 0;

try {
    $stmt = $pdo->prepare("INSERT INTO projets (titre, description, link, ordre) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $link, $maxOrdre]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Projet ajoutée', 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
