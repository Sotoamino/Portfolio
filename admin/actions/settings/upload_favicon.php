<?php
header('Content-Type: application/json');

$uploadDir = '../../../assets/images/';
$targetFile = $uploadDir . 'favicon.ico';

if (!isset($_FILES['favicon'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier favicon reçu.']);
    exit;
}

$file = $_FILES['favicon'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload (code ' . $file['error'] . ').']);
    exit;
}

// Vérifie le type MIME
$tmpPath = $file['tmp_name'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

// Autorise PNG et JPEG uniquement
$allowedTypes = ['image/png', 'image/jpeg'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Le fichier doit être une image PNG ou JPEG.']);
    exit;
}

// Crée le dossier s’il n’existe pas
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier de destination.']);
        exit;
    }
}

// Convertit en .ico avec Imagick
try {
    $image = new Imagick($tmpPath);
    $image->setImageFormat('ico');
    $image->resizeImage(64, 64, Imagick::FILTER_LANCZOS, 1, true); // Redimensionne proprement
    $image->writeImage($targetFile);
    $image->destroy();

    echo json_encode(['success' => true, 'message' => '✅ Fichier favicon.ico généré avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la conversion en .ico : ' . $e->getMessage()]);
}
