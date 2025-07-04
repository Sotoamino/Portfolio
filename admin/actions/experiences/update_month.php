<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['display_month'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètre manquant']);
    exit;
}

$settingsPath =  './../../tools/settings.json';

if (!file_exists($settingsPath)) {
    echo json_encode(['success' => false, 'message' => 'Fichier de configuration introuvable']);
    exit;
}

$settings = json_decode(file_get_contents($settingsPath), true);
$settings['display_month'] = (bool)$data['display_month'];

if (file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Échec de la sauvegarde']);
}