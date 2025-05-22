<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../include/sqlconnect.php';

$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM langues WHERE id = :id");
    $stmt->execute(['id' => $id]);


    echo json_encode(['success' => true, 'message' => 'Langue supprimée avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
}
