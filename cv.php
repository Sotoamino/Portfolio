<?php



require_once 'include/sqlconnect.php';
require_once 'include/phpqrcode/qrlib.php';

$qrFile = 'assets/qr.png';
$qrUrl = 'https://salamagnon.fr';
QRcode::png($qrUrl, $qrFile, QR_ECLEVEL_L, 4);
$data = $pdo->query("SELECT * FROM settings LIMIT 1")->fetchAll();

$template = file_get_contents('./files/cv-template.html');
foreach ($data as $key => $value) {
    $template = str_replace('{{' . $key . '}}', $value, $template);
}
$template = str_replace('{{qr}}', $qrFile, $template);

if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    require 'include/fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $data['nom'] . ' ' . $data['prenom'], 0, 1);
    $pdf->Cell(0, 10, $data['email'] . ' | ' . $data['telephone'], 0, 1);
    $pdf->Image($qrFile, 10, 40, 30, 30);
    $pdf->Output('D', 'CV_Liam_Salamagnon.pdf');
    exit;
}

echo $template;
?>