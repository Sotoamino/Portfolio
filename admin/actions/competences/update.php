<?php

require_once '../../../tools/sqlconnect.php';

$id = $_POST['id'] ?? '';
$nom = $_POST['nom'] ?? '';
$niveau = $_POST['niveau'] ?? '';

if (!$id || !$nom || !is_numeric($niveau)) {
    echo json_encode(['success' => false, 'message' => 'ID, nom ou niveau invalide.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE competences SET nom = :nom, niveau = :niveau WHERE id = :id");
    $stmt->execute(['nom' => $nom, 'niveau' => $niveau, 'id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Compétence mise à jour avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
