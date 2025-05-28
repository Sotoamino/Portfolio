<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once '../../../tools/sqlconnect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
    exit;
}

$check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE id = 1");
$check->execute();
$exists = $check->fetchColumn() > 0;

if (!empty($data['linkedin'])) {
    if (preg_match('#/in/([^/?]+)#', $data['linkedin'], $matches)) {
        $data['linkedin'] = $matches[1];
    }
}
if (!empty($data['github'])) {
    if (preg_match('#.com/([^/?]+)#', $data['github'], $matches)) {
        $data['github'] = $matches[1];
    }
}

if (!$exists) {
    $insert = $pdo->prepare("INSERT INTO settings (id, first_name, last_name, email, phone, location, linkedin, instagram, github, twitter, discord, keywords, particle_config)
        VALUES (1, :first_name, :last_name, :email, :phone, :location, :linkedin, :instagram, :github, :twitter, :discord, :keywords, :particle_config)");
    $insert->execute([
        ':first_name' => $data['first_name'] ?? '',
        ':last_name' => $data['last_name'] ?? '',
        ':email' => $data['email'] ?? '',
        ':phone' => $data['phone'] ?? '',
        ':location' => $data['location'] ?? '',
        ':linkedin' => $data['linkedin'] ?? '',
        ':instagram' => $data['instagram'] ?? '',
        ':github' => $data['github'] ?? '',
        ':twitter' => $data['twitter'] ?? '',
        ':discord' => $data['discord'] ?? '',
        ':keywords' => is_array($data['keywords']) ? implode(',', $data['keywords']) : $data['keywords'],
        ':particle_config' => $data['particle_config'] ?? 'default.json'
    ]);
} else {
    $update = $pdo->prepare("UPDATE settings SET 
        first_name = :first_name,
        last_name = :last_name,
        email = :email,
        phone = :phone,
        location = :location,
        linkedin = :linkedin,
        instagram = :instagram,
        github = :github,
        twitter = :twitter,
        discord = :discord,
        keywords = :keywords,
        particle_config = :particle_config
        WHERE id = 1");
    $update->execute([
        ':first_name' => $data['first_name'] ?? '',
        ':last_name' => $data['last_name'] ?? '',
        ':email' => $data['email'] ?? '',
        ':phone' => $data['phone'] ?? '',
        ':location' => $data['location'] ?? '',
        ':linkedin' => $data['linkedin'] ?? '',
        ':instagram' => $data['instagram'] ?? '',
        ':github' => $data['github'] ?? '',
        ':twitter' => $data['twitter'] ?? '',
        ':discord' => $data['discord'] ?? '',
        ':keywords' => is_array($data['keywords']) ? implode(',', $data['keywords']) : $data['keywords'],
        ':particle_config' => $data['particle_config'] ?? 'default.json'
    ]);
}

echo json_encode(['success' => true]);
