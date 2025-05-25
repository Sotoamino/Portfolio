<?php
require_once 'tools/sqlconnect.php';

$maintenance = $pdo->query("SELECT maintenance_status FROM settings LIMIT 1")
                   ->fetchColumn();

if ($maintenance == 1) {
  header("Location: maintenance.php");
  exit;
}
?>
<?php

$id = $_GET['id'] ?? null;
$experience = null;
$error = null;

if (!$id) {
    $error = "ID manquant.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE id = ?");
    $stmt->execute([$id]);
    $experience = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($experience['content'] == null) {
        $error = "La présentation n'a pas encore été rédigé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Prévisuel</title>
    <link rel="stylesheet" href="./assets/css/missions.css">
</head>
<body>
    <?php if ($error): ?>
        <div class="mission-error">
            <h2>Oups...</h2>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php else: ?>
        <?= $experience['content'] ?>
    <?php endif; ?>
</body>
</html>