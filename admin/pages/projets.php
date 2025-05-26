<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../tools/sqlconnect.php';

// Récupérer toutes les expériences ordonnées par 'ordre'
$stmt = $pdo->query("SELECT * FROM projets ORDER BY `ordre` ASC");
$projets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/projets.css">

<h2>Gestion des expériences</h2>

<div style="display:none" id="message"></div>

<table class="projet-table">
  <thead>
    <tr>
      <th style="width:20px"></th> <!-- Poignée pour drag & drop -->
      <th>Titre</th>
      <th>Description</th>
      <th>Lien</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="projet-list">
    <?php foreach ($projets as $proj): ?>
      <tr data-id="<?= $proj['id'] ?>" class="sortable-item">
        <td class="drag-handle">⋮⋮</td>
        <td><input type="text" class="titre" value="<?= htmlspecialchars($proj['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
        <td><input type="text" class="description" value="<?= htmlspecialchars($proj['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
        <td><input type="text" class="link" value="<?= htmlspecialchars($proj['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>

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
  <input type="text" id="projet-titre" name="titre" placeholder="Titre" required>
  <input type="text" id="projet-description" name="description" placeholder="description" required>
  <input type="text" id="projet-link" name="link" placeholder="https://exemple.com..." >
  <button type="submit">Ajouter</button>
</form>

<!-- Charger jQuery, Sortable.js et ton script JS séparé -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
