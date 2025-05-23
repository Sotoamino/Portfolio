<?php
session_start();
header('Content-Type: application/json');
require_once '../../include/htmlpurifier/library/HTMLPurifier.auto.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Non autorisé']);
    exit;
}

require_once '../../tools/sqlconnect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['content'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

// Nettoyage avec HTMLPurifier
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$cleanContent = $purifier->purify($data['content']);

$stmt = $pdo->prepare("UPDATE experiences SET content = :content WHERE id = :id");
$stmt->execute([
    'content' => $cleanContent,
    'id' => $data['id']
]);

echo json_encode(['message' => 'Sauvegarde réussie']);
