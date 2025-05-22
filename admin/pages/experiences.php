<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../tools/sqlconnect.php';

// Récupérer toutes les expériences ordonnées par 'ordre'
$stmt = $pdo->query("SELECT * FROM experiences ORDER BY `ordre` ASC");
$experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/experiences.css">

<h2>Gestion des expériences</h2>
<div style="font-size:10px">
<p>Vous pouvez ajouter, modifier ou supprimer des expériences. Les expériences sont affichées dans l'ordre où elles apparaissent dans la base de données.</p>
<p>Pour modifier l'ordre, faites glisser les lignes avec la poignée de gauche.</p>
<p>Pour ajouter une nouvelle expérience, remplissez le formulaire ci-dessous.</p>
<p>Pour modifier le contenu d'une expérience, cliquez sur le bouton "Éditer" à droite de l'expérience.</p>
<p>Pour supprimer une expérience, cliquez sur le bouton "Supprimer".</p>
<p>Pour mettre à jour l'ordre des expériences, cliquez sur le bouton "Enregistrer" après avoir déplacé les lignes.</p>
<p>Pour ajouter des tags, écrivez votre tag et cloturez avec une virgule.</p>
</div>
<div style="display:none" id="message"></div>

<table class="experience-table">
  <thead>
    <tr>
      <th style="width:20px"></th> <!-- Poignée pour drag & drop -->
      <th>Titre</th>
      <th>Entreprise</th>
      <th>Début</th>
      <th>Fin</th>
      <th>Tags</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="experience-list">
    <?php foreach ($experiences as $exp): ?>
      <tr data-id="<?= $exp['id'] ?>" class="sortable-item">
        <td class="drag-handle">⋮⋮</td>
        <td><input type="text" class="titre" value="<?= htmlspecialchars($exp['titre']) ?>"></td>
        <td><input type="text" class="entreprise" value="<?= htmlspecialchars($exp['entreprise']) ?>"></td>
        <td><input type="date" class="startDate" value="<?= htmlspecialchars($exp['startDate']) ?>"></td>
        <td>
          <input type="date" class="endDate" value="<?= htmlspecialchars($exp['endDate']) ?>">
          <br/>
          <button type="button" class="clear-endDate" title="Mettre en cours">En cours</button>
        </td>
<td>
  <div class="tag-input-container" contenteditable="false">
    <?php
      $tags = array_filter(array_map('trim', explode(',', $exp['tags'] ?? '')));
      foreach ($tags as $tag) {
          echo '<span class="tag">' . htmlspecialchars($tag) . '<span class="remove-tag">&times;</span></span>';
      }
    ?>
    <input type="text" class="tag-input" placeholder="php, js...">
  </div>
</td>      <td>
          <button class="open-editor" data-id="<?= $exp['id'] ?>">✏️ Éditer</button>
          <button class="save">💾</button>
          <button class="delete">🗑️</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Ajouter une expérience</h3>
<form id="add-form">
  <input type="text" id="experience-titre" name="titre" placeholder="Titre" required>
  <input type="text" id="experience-entreprise" name="entreprise" placeholder="Entreprise" required>
  <input type="date" id="experience-startDate" name="startDate" required>
  <input type="date" id="experience-endDate" name="endDate" placeholder="Fin (laisser vide si en cours)">
  <input type="text" id="experience-tags" name="tags" placeholder="Tags (ex: php, javascript)">
  <button type="submit">Ajouter</button>
</form>

<!-- Charger jQuery, Sortable.js et ton script JS séparé -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
