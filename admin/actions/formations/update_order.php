<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
require_once '../../../tools/sqlconnect.php';

$ids = $_POST['ids'] ?? [];

if (!is_array($ids) || count($ids) === 0) {
    echo json_encode(['success' => false, 'message' => 'Liste d\'IDs invalide']);
    exit;
}

try {
    $pdo->beginTransaction();
    $ordre = 0;
    $stmt = $pdo->prepare("UPDATE formations SET ordre = ? WHERE id = ?");
    foreach ($ids as $id) {
        $stmt->execute([$ordre, (int)$id]);
        $ordre++;
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Ordre mis à jour']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
