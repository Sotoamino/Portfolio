<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
require_once '../../../tools/sqlconnect.php';

$data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
$titre = trim($data['titre'] ?? '');
$name = trim($data['name'] ?? '');
$startDate = !empty($data['startDate']) ? $data['startDate'] : null;
$endDate = isset($data['endDate']) && $data['endDate'] !== '' ? $data['endDate'] : null;
$link = trim($data['link'] ?? '');
$description = trim($data['description'] ?? '');

if ($id <= 0 || $titre === '' || $name === '') {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE formations SET titre = ?, name = ?, startDate = ?, endDate = ?, link = ?, description = ? WHERE id = ?");
    $stmt->execute([$titre, $name, $startDate, $endDate, $link, $description, $id]);
    echo json_encode(['success' => true, 'message' => 'Formation mise à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
