<?php
require_once '../../../tools/sqlconnect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$allowed = ['linkedin_status', 'github_status', 'instagram_status', 'twitter_status', 'discord_status', 'maintenance_status'];
$name = $data['name'] ?? null;
$value = isset($data['value']) ? (int)$data['value'] : null;

if ($name && in_array($name, $allowed)) {
    $stmt = $pdo->prepare("UPDATE settings SET `$name` = :value WHERE id = 1");
    $stmt->execute(['value' => $value]);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètre invalide']);
}