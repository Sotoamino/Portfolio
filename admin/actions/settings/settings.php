<?php
session_start();
require_once '../../../tools/sqlconnect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$allowed = ['linkedin_status', 'github_status', 'instagram_status', 'twitter_status', 'discord_status', 'maintenance_status', 'particle_config'];
$name = $data['name'] ?? null;
$value = $data['value'] ?? null;

if (!$name || !in_array($name, $allowed)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Paramètre invalide',
        'received_name' => $name,
        'allowed' => $allowed
    ]);
    exit;
}

if ($name === 'particle_config') {
    // Validation stricte des fichiers JSON autorisés
    $particleDir = '../../../assets/particles/';
    $validParticles = [];
    if (is_dir($particleDir)) {
        $files = scandir($particleDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $validParticles[] = $file;
            }
        }
    }
    if (!in_array($value, $validParticles)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valeur particle_config invalide']);
        exit;
    }
} else {
    // Pour les toggles, cast en int (0 ou 1)
    $value = (int)$value;
}

$stmt = $pdo->prepare("UPDATE settings SET `$name` = :value WHERE id = 1");
$stmt->execute(['value' => $value]);

echo json_encode(['success' => true]);
