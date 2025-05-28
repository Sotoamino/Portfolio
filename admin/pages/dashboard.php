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
$columnCheck = $pdo->query("SHOW COLUMNS FROM settings LIKE 'particle_config'")->fetch(PDO::FETCH_ASSOC);
if (!$columnCheck) {
    $pdo->exec("ALTER TABLE settings ADD COLUMN particle_config VARCHAR(255) DEFAULT 'default.json'");
    $pdo->exec("UPDATE settings SET particle_config = 'default.json' WHERE id = 1");
}
// --- Gestion du POST pour mise à jour des settings ---
$particleDir = '../../assets/particles/';
$particleFiles = [];

if (is_dir($particleDir)) {
    $files = scandir($particleDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $particleFiles[] = $file;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Récupérer les checkbox (envoyés uniquement si cochés)
    $maintenance_status = isset($_POST['maintenance_status']) ? 1 : 0;
    $github_status = isset($_POST['github_status']) ? 1 : 0;
    $linkedin_status = isset($_POST['linkedin_status']) ? 1 : 0;

    // Particle config sélectionnée
    $particle_config = $_POST['particle_config'] ?? null;
    if (!in_array($particle_config, $particleFiles)) {
        $particle_config = null; // sécurité
    }

    // Mise à jour en base
    $stmt = $pdo->prepare("UPDATE settings SET maintenance_status = ?, github_status = ?, linkedin_status = ?, particle_config = ? WHERE id = 1");
    $stmt->execute([$maintenance_status, $github_status, $linkedin_status, $particle_config]);

    // Rechargement de la page pour voir les changements
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
// Récupération des paramètres
$settings = $pdo->query("SELECT maintenance_status, github_status, linkedin_status, particle_config FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
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
  <div class="card" id="update-gh">
    <p>Chargement...</p>
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

   <div class="card">
<label class="switch-label" for="particleConfigSelect">Configuration Bannière</label>
    <select id="particleConfigSelect" name="particle_config" required>
  <?php foreach ($particleFiles as $file): 
    $displayName = pathinfo($file, PATHINFO_FILENAME); // nom sans extension
  ?>
    <option value="<?= htmlspecialchars($file) ?>" <?= ($settings['particle_config'] === $file) ? 'selected' : '' ?>>
      <?= htmlspecialchars($displayName) ?>
    </option>
  <?php endforeach; ?>
</select>
  </div>
</div>

</body>
</html>
