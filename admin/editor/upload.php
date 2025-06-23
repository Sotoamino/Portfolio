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

    // Chemin du dossier d’upload avec sécurité
    $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/images/';

    // Crée le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Nettoyage et renommage sécurisé du fichier
    $originalName = basename($_FILES['file']['name']);
    $cleanName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $originalName);
    $fileName = uniqid('img_', true) . '_' . $cleanName;

    $targetFile = $uploadDir . $fileName;

    // Types autorisés (par vérification réelle)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // Détection réelle du type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
        exit;
    }

    // Déplacement du fichier
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        // URL publique (chemin absolu depuis la racine du site)
        $url = '/assets/images/' . $fileName;

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
