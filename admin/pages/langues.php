<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // ou 401
    echo "Accès interdit.";
    exit;
}
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: text/html; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(0);
require_once '../../tools/sqlconnect.php';

$stmt = $pdo->query("SELECT * FROM langues ORDER BY `ordre` ASC");
$langues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/langues.css">

<h2>Gestion des compétences</h2>
<div style="display:none" id="message"></div>

<table class="langue-table">
  <thead>
    <tr>
          <th></th> <!-- Pour la poignée -->
      <th>Nom</th>
      <th>Niveau</th>
      <th>Actions</th>
    </tr>
  </thead>
<tbody id="langue-list">
  <?php foreach ($langues as $c): ?>
  <tr data-id="<?= $c['id'] ?>" class="sortable-item">
      <td class="drag-handle">⋮⋮</td>
    <td>
      <input type="text" class="nom" value="<?= htmlspecialchars($c['nom']) ?>">
    </td>
    <td>
      <input type="range" class="niveau" min="0" max="100" value="<?= $c['niveau'] ?>">
      <span class="niveau-val"><?= $c['niveau'] ?></span>%
    </td>
    <td>
      <button class="save">💾</button>
      <button class="delete">🗑️</button>
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>


</table>

<h3>Ajouter une langue</h3>
<form id="add-form">
  <input type="text" id="langue-name" name="nom" placeholder="Nom" required>
  <input type="number" id="langue-level" name="niveau" placeholder="Niveau" min="0" max="100" required>
  <button type="submit">Ajouter</button>
</form>

