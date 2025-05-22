<?php
ini_set('display_errors', 0); // Ne pas afficher dans le navigateur
ini_set('log_errors', 1);     // Loguer dans error_log
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

// Vérifier si la ligne id = 1 existe déjà
$check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE id = 1");
$check->execute();
$exists = $check->fetchColumn() > 0;
if (!empty($data['linkedin'])) {
    // Exemples valides : https://linkedin.com/in/johndoe, linkedin.com/in/johndoe/, /in/johndoe
    if (preg_match('#/in/([^/?]+)#', $data['linkedin'], $matches)) {
        $data['linkedin'] = $matches[1]; // on garde juste "johndoe"
    }
}
if (!empty($data['github'])) {
    // Exemples valides : https://github.com/johndoe, github.com/in/johndoe/
    if (preg_match('#.com/([^/?]+)#', $data['github'], $matches)) {
        $data['github'] = $matches[1]; // on garde juste "johndoe"
    }
}
// Si elle n'existe pas, on insère une nouvelle ligne
if (!$exists) {
    $insert = $pdo->prepare("INSERT INTO settings (id, first_name, last_name, email, phone, location, linkedin, instagram, github, twitter, discord, keywords)
        VALUES (1, :first_name, :last_name, :email, :phone, :location, :linkedin, :instagram, :github, :twitter, :discord, :keywords)");
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
        ':keywords' => is_array($data['keywords']) ? implode(',', $data['keywords']) : $data['keywords']
    ]);
} else {
    // Sinon on met à jour
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
        keywords = :keywords
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
        ':keywords' => is_array($data['keywords']) ? implode(',', $data['keywords']) : $data['keywords']
    ]);
}

echo json_encode(['success' => true]);
