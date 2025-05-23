<?php
require_once './tools/sqlconnect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die('ID manquant');
}

$stmt = $pdo->prepare("SELECT content FROM experiences WHERE id = ?");
$stmt->execute([$id]);
$experience = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$experience) {
    die('Expérience introuvable');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Prévisuel</title>
    <link rel="stylesheet" href="/assets//css/experiences.css">


</head>
<body>
        <?= $experience['content'] ?>
</body>