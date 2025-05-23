<?php
session_start();
require_once '../../tools/sqlconnect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// On récupère les données existantes
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour retourner uniquement le nom d'utilisateur LinkedIn
function getLinkedinUsername($url) {
    $parts = explode("/in/", $url);
    return $parts[1] ?? $url;
}

function getGithubUsername($url) {
    $parts = explode("com/", $url);
    return $parts[1] ?? $url;
}

// Tableau des réseaux supportés et leurs icônes FontAwesome
$supportedSocials = [
    'linkedin' => 'fab fa-linkedin',
    'instagram' => 'fab fa-instagram',
    'github' => 'fab fa-github',
    'twitter' => 'fab fa-twitter',
    'discord' => 'fab fa-discord'
];

// Préparer mots-clés en tableau
$keywords = array_filter(array_map('trim', explode(',', $settings['keywords'] ?? '')));

?>

<link rel="stylesheet" href="./assets/css/informations.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<div class="content">
  <form id="settings-form">

    <!-- Informations personnelles -->
    <div class="section">
      <h3>Informations personnelles</h3>
      <div class="form-grid">
        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($settings['first_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="last_name" value="<?= htmlspecialchars($settings['last_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="phone">Téléphone</label>
          <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="localisation">Localisation</label>
          <input type="text" id="localisation" name="location" value="<?= htmlspecialchars($settings['location'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- Réseaux sociaux -->
    <div class="section">
      <h3>Réseaux sociaux</h3>
      <div id="socials-container">
        <?php
        foreach ($supportedSocials as $key => $icon) {
            $val = trim($settings[$key] ?? '');
            if (!$val) continue;

            // Pour LinkedIn on garde uniquement le nom d'utilisateur dans la value, mais affiche le lien complet dans le champ
          if ($key === 'linkedin') {
              $displayValue = 'https://linkedin.com/in/' . getLinkedinUsername($val);
          } elseif ($key === 'github') {
              $displayValue = 'https://github.com/' . getGithubUsername($val);
          } else {
              $displayValue = $val;
}

            echo '<div class="social-input" data-network="'. $key .'">';
            echo '<i class="'. $icon .'"></i>';
            echo '<input type="text" name="'. $key .'" placeholder="https://'. $key .'.com/..." value="'. htmlspecialchars($displayValue) .'" oninput="extractLinkedin(this)">';
            echo '<button type="button" class="remove-social" onclick="removeSocial(this)">×</button>';
            echo '</div>';
        }
        ?>
      </div>
      <button type="button" id="add-social">+ Ajouter un réseau</button>
    </div>
<!-- Popup ajout réseau -->
<div id="add-social-popup" class="popup-overlay" style="display:none;">
  <div class="popup-content">
    <div class="social-choice-group">
      <?php foreach ($supportedSocials as $key => $icon): ?>
        <button type="button" class="social-choice" data-network="<?= $key ?>" title="<?= ucfirst($key) ?>">
          <i class="<?= $icon ?>"></i>
        </button>
      <?php endforeach; ?>
    </div>
    <button type="button" id="close-popup">Annuler</button>
  </div>
</div>


    <!-- Mots-clés -->
    <div class="section">
      <h3>Mots-clés</h3>
      <div class="form-group">
        <label for="keywords">Ajouter des mots-clés</label>
        <input type="text" id="keyword-input" placeholder="Tapez un mot-clé et appuyez sur Entrée">
        <div id="keywords-container">
          <?php foreach ($keywords as $kw): ?>
            <span class="keyword-tag"><?= htmlspecialchars($kw) ?><button type="button" class="remove-keyword">×</button></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Bouton -->
    <button type="submit">Enregistrer</button>
  </form>
</div>