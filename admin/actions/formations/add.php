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
$name = trim($data['name'] ?? '');
$startDate = !empty($data['startDate']) ? $data['startDate'] : null;
$endDate = isset($data['endDate']) && $data['endDate'] !== '' ? $data['endDate'] : null;
$link = trim($data['link'] ?? '');

if ($titre === '' || $name === '') {
    echo json_encode(['success' => false, 'message' => 'Titre et etablissement obligatoires']);
    exit;
}

// Trouver l'ordre max actuel
$maxOrdre = $pdo->query("SELECT MAX(ordre) FROM formations")->fetchColumn();
$maxOrdre = $maxOrdre !== false ? (int)$maxOrdre + 1 : 0;

try {
    $stmt = $pdo->prepare("INSERT INTO formations (titre, name, startDate, endDate, link, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $name, $startDate, $endDate, $link, $maxOrdre]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Formation ajoutée', 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
