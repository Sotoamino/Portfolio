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

// Récupérer toutes les expériences ordonnées par 'ordre'
$stmt = $pdo->query("SELECT * FROM formations ORDER BY `ordre` ASC");
$formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/formations.css">

<h2>Gestion des expériences</h2>
<div style="display:none" id="message"></div>

<table class="formation-table">
  <thead>
    <tr>
      <th style="width:20px"></th> <!-- Poignée pour drag & drop -->
      <th>Diplome</th>
      <th>Etablissement</th>
      <th>Début</th>
      <th>Fin</th>
      <th>Lien de la formation</th>
      <th>Description</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="formation-list">
    <?php foreach ($formations as $forma): ?>
      <tr data-id="<?= $forma['id'] ?>" class="sortable-item">
                <td class="drag-handle">⋮⋮</td>

<td><input type="text" class="titre" value="<?= htmlspecialchars($forma['titre']) ?>"></td>
<td><input type="text" class="name" value="<?= htmlspecialchars($forma['name']) ?>"></td>
<td><input type="date" class="startDate" value="<?= htmlspecialchars($forma['startDate']) ?>"></td>
<td>
  <input type="date" class="endDate" value="<?= htmlspecialchars($forma['endDate']) ?>">
  <button type="button" class="clear-endDate" title="Mettre en cours">En cours</button>
</td>
<td><input type="text" class="formation-link" value="<?= htmlspecialchars($forma['link']) ?>"></td>
<td><textarea class="description"><?= htmlspecialchars($forma['description']) ?></textarea></td>
<td>
          <button class="save">💾</button>
          <button class="delete">🗑️</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Ajouter une expérience</h3>
<form id="add-form">
  <input type="text" id="formation-titre" name="titre" placeholder="Diplome" required>
  <input type="text" id="formation-name" name="name" placeholder="Etablissement" required>
  <input type="date" id="formation-startDate" name="startDate" required>
  <input type="date" id="formation-endDate" name="endDate" placeholder="Fin (laisser vide si en cours)">
  <input type="text" id="formation-link" name="tags" placeholder="Lien de la formation">
  <textarea id="formation-description" name="description" placeholder="Description"></textarea>
  <button type="submit">Ajouter</button>
</form>

<!-- Charger jQuery, Sortable.js et ton script JS séparé -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
