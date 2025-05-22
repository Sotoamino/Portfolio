<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../include/sqlconnect.php';


$ids = $_POST['ids'] ?? [];

if (!is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

try {
    foreach ($ids as $ordre => $id) {
        $stmt = $pdo->prepare("UPDATE langues SET ordre = :ordre WHERE id = :id");
        $stmt->execute(['ordre' => $ordre, 'id' => $id]);
    }


    echo json_encode(['success' => true, 'message' => 'Ordre mis à jour.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
