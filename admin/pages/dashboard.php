<?php
require_once '../../tools/sqlconnect.php';

// Récupération des paramètres
$settings = $pdo->query("SELECT maintenance_status, github_status, linkedin_status FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <style>
    #notification { margin-bottom: 20px; padding: 10px; border-radius: 5px; }
    #notification.success { background-color: #d4edda; color: #155724; }
    #notification.error { background-color: #f8d7da; color: #721c24; }
  </style>
  <link rel="stylesheet" href="assets/css/dashboard.css">

</head>
<body>

<h1>Dashboard</h1>
<div id="notification"></div>
<div class="cards-container">
  <div class="card">
    <h2>Mise à jour du site</h2>
    <form method="POST" action="/admin/update/update.php">
      <button type="submit">🔄 Mettre à jour depuis GitHub</button>
    </form>
    <form method="POST" action="/admin/update/rollback.php" style="margin-top: 1rem;">
      <button type="submit">↩️ Restaurer la dernière sauvegarde</button>
    </form>
  </div>

  <div class="card">
    <h2>Importer un CV</h2>
    <form id="uploadForm" class="cv-upload-form" enctype="multipart/form-data" method="POST" action="upload_cv.php">
      <label for="cv">Fichier PDF uniquement :</label>
      <input type="file" name="cv" id="cv" accept="application/pdf" required />
      <button type="submit">Envoyer</button>
    </form>
  </div>

  <div class="card">
    <h2>Paramètres du site</h2>

    <div class="switch-container">
      <label class="switch-label" for="maintenanceToggle">Mode maintenance</label>
      <label class="switch">
        <input type="checkbox" id="maintenanceToggle" <?= $settings['maintenance_status'] ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
    </div>

    <div class="switch-container">
      <label class="switch-label" for="githubToggle">GitHub</label>
      <label class="switch">
        <input type="checkbox" id="githubToggle" <?= $settings['github_status'] ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
    </div>

    <div class="switch-container">
      <label class="switch-label" for="linkedinToggle">LinkedIn</label>
      <label class="switch">
        <input type="checkbox" id="linkedinToggle" <?= $settings['linkedin_status'] ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
    </div>
  </div>

  <div class="card">
    <h2>Ressources</h2>
    <p>
      Le tuto d'utilisation est disponible
      <a href="https://github.com/Sotoamino/Portfolio/blob/main/README.md" target="_blank">ici</a>.
    </p>
    <p>
      Ce site est en cours de développement, certaines fonctionnalités peuvent être incomplètes.
    </p>
  </div>
</div>

</body>
</html>
