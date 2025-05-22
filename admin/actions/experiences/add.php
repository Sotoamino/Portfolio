<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
require_once '../../../include/sqlconnect.php';

$data = $_POST;

$titre = trim($data['titre'] ?? '');
$entreprise = trim($data['entreprise'] ?? '');
$startDate = !empty($data['startDate']) ? $data['startDate'] : null;
$endDate = isset($data['endDate']) && $data['endDate'] !== '' ? $data['endDate'] : null;
$tags = trim($data['tags'] ?? '');  // ajout du tags

if ($titre === '' || $entreprise === '') {
    echo json_encode(['success' => false, 'message' => 'Titre et entreprise obligatoires']);
    exit;
}

// Trouver l'ordre max actuel
$maxOrdre = $pdo->query("SELECT MAX(ordre) FROM experiences")->fetchColumn();
$maxOrdre = $maxOrdre !== false ? (int)$maxOrdre + 1 : 0;

try {
    $stmt = $pdo->prepare("INSERT INTO experiences (titre, entreprise, startDate, endDate, ordre, tags) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $entreprise, $startDate, $endDate, $maxOrdre, $tags]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Expérience ajoutée', 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
