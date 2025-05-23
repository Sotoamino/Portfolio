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
$entreprise = trim($data['description'] ?? '');
$link = !empty($data['link']) ? $data['link'] : null;

if ($id <= 0 || $titre === '' || $entreprise === '') {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE projets SET titre = ?, description = ?, link = ? WHERE id = ?");
    $stmt->execute([$titre, $description, $link, $id]);
    echo json_encode(['success' => true, 'message' => 'Projet mise à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
