<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
require_once '../../../include/sqlconnect.php';

$data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
$titre = trim($data['titre'] ?? '');
$entreprise = trim($data['entreprise'] ?? '');
$startDate = !empty($data['startDate']) ? $data['startDate'] : null;
$endDate = isset($data['endDate']) && $data['endDate'] !== '' ? $data['endDate'] : null;
$tags = trim($data['tags'] ?? '');  // récupération tags

if ($id <= 0 || $titre === '' || $entreprise === '') {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE experiences SET titre = ?, entreprise = ?, startDate = ?, endDate = ?, tags = ? WHERE id = ?");
    $stmt->execute([$titre, $entreprise, $startDate, $endDate, $tags, $id]);
    echo json_encode(['success' => true, 'message' => 'Expérience mise à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
