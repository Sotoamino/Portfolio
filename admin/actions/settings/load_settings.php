<?php
require_once '../../../tools/sqlconnect.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT maintenance_status, github_status, linkedin_status FROM settings WHERE id = 1");
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));