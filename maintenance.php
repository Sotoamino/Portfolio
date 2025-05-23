<?php
require_once 'tools/sqlconnect.php';

$maintenance = $pdo->query("SELECT maintenance_status FROM settings LIMIT 1")
                   ->fetchColumn();

if ($maintenance != 1) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Maintenance</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 100px;
      background-color: #f5f5f5;
    }
    .box {
      background: white;
      padding: 40px;
      border-radius: 10px;
      display: inline-block;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>🛠️ Site en maintenance</h1>
    <p>Nous revenons très vite ! Merci pour votre patience.</p>
  </div>
</body>
</html>