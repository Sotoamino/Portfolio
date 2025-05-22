<?php
session_start();

// Sécurité : vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

// Vérifie que la méthode POST contient un fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . 'assets/images/';

    // Crée le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($_FILES['file']['name']);

    // Sécurisation du nom du fichier (optionnel mais recommandé)
    $fileName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);

    $targetFile = $uploadDir . $fileName;

    // Types autorisés
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($_FILES['file']['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
        exit;
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        // Renvoie l’URL utilisable côté web
        $url = '../../../assets/images/' . $fileName;

        echo json_encode([
            'success' => true,
            'file' => [
                'url' => $url
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
}


