<?php
header('Content-Type: application/json');

$uploadDir = '../../../assets/files/';
$targetFile = $uploadDir . 'cv.pdf';

if (!isset($_FILES['cv'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu.']);
    exit;
}

$file = $_FILES['cv'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erreur d\'upload (code ' . $file['error'] . ').']);
    exit;
}

$tmpPath = $file['tmp_name'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if ($fileType !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Le fichier doit être un PDF.']);
    exit;
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (move_uploaded_file($tmpPath, $targetFile)) {
    echo json_encode(['success' => true, 'message' => '✅ Fichier importé avec succès en tant que cv.pdf.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier.']);
}

