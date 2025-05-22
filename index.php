<?php
require './tools/sqlconnect.php';
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération de toutes les données nécessaires
$settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$experiences = $pdo->query("SELECT * FROM experiences ORDER BY ordre ASC")->fetchAll();
$skills = $pdo->query("SELECT * FROM competences ORDER BY ordre ASC")->fetchAll();
$langues = $pdo->query("SELECT * FROM langues ORDER BY ordre ASC")->fetchAll();
$formations = $pdo->query("SELECT * FROM formations ORDER BY ordre ASC")->fetchAll();
$projects = $pdo->query("SELECT * FROM projets ORDER BY ordre ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['first_name']) ?> <?= htmlspecialchars($settings['last_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
</head>
<body>
<nav>
  <div class="nav-header">
    <a href="#">Accueil</a>
    <button class="menu-toggle" id="menu-toggle" aria-label="Menu">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
  </div>
  <ul class="nav-links" id="nav-links">
    <li><a href="#skills">Compétences</a></li>
    <li><a href="#experience">Expériences</a></li>
    <li><a href="#projects">Projets</a></li>
    <li><a href="#education">Formation</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
</nav>

<header>
    <div id="particles-js"></div>
    <div class="intro">
        <h1><?= htmlspecialchars($settings['first_name']) ?> <?= htmlspecialchars($settings['last_name']) ?></h1>
        <p><span id="changing-text"></span><span id="cursor"> |</span></p>
    </div>
</header>
<div class="cv-download">
    <a href="./assets/files/cv.pdf" download="<?= htmlspecialchars($settings['first_name']) ?>_<?= htmlspecialchars($settings['last_name']) ?>_CV" class="download-btn">
        <i class="fas fa-download"></i> Télécharger mon CV
    </a>
</div>
<section id="skills">
    <h2>Compétences</h2>
    <div class="card">
<?php foreach ($skills as $skill): ?>
    <div class="progress">
        <div class="progress-bar" data-percent="<?= htmlspecialchars($skill['niveau']) . "%" ?>">
        <span class="skill-name"><?= htmlspecialchars($skill['nom']) ?></span>
			</div>
    </div>
<?php endforeach; ?>
    </div>
    <h2>Langues</h2>
    <div class="card">
        <?php foreach ($langues as $langue): ?>
            <div class="progress">				
				<div class="progress-bar" data-percent="<?= htmlspecialchars($langue['niveau']) . "%" ?>">
        <span class="skill-name"><?= htmlspecialchars($langue['nom']) ?></span>
			</div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="experience">
    <h2>Expériences Professionnelles</h2>
    <?php foreach ($experiences as $exp): ?>
        <div class="card">
            <h3><?= htmlspecialchars($exp['entreprise']) ?> - <?= htmlspecialchars($exp['titre']) ?></h3>
            <?php
            $start = date('d/m/Y', strtotime($exp['startDate']));
            $endDateValid = !empty($exp['endDate']) && date('d/m/Y', strtotime($exp['endDate'])) !== '30/11/-0001';
            $end = $endDateValid ? date('d/m/Y', strtotime($exp['endDate'])) : 'En cours';
            ?>
            <p><?= $start ?> - <?= $end ?></p>

            <?php if (!empty($exp["tags"])): ?>
                <div class="tags">
                    <?php
                    $tags = array_map('trim', explode(',', $exp["tags"]));
                    foreach ($tags as $tag):
                        if ($tag !== ''):
                    ?>
                        <span class="tag"><?= htmlspecialchars($tag) ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            <?php endif; ?>

            <button class="view-missions-btn" onclick="window.open('missions.php?id=<?= $exp['id'] ?>', 'missionsPopup', 'width=800,height=600,resizable=yes,scrollbars=yes')">
                En savoir plus
            </button>
        </div>
    <?php endforeach; ?>
</section>

<section id="formations">
    <h2>Formations</h2>
    <?php foreach ($formations as $forma): ?>
        <div class="card">
            <h3><?= htmlspecialchars($forma['name']) ?> - <?= htmlspecialchars($forma['titre']) ?></h3>
            <?php
            $start = date('d/m/Y', strtotime($forma['startDate']));
            $endDateValid = !empty($forma['endDate']) && date('d/m/Y', strtotime($exp['endDate'])) !== '30/11/-0001';
            $end = $endDateValid ? date('d/m/Y', strtotime($forma['endDate'])) : 'En cours';
            ?>
            <p><?= $start ?> - <?= $end ?></p>

            <button class="view-missions-btn" onclick="window.open('<?= $forma['link'] ?>', 'formationsPopup', 'width=800,height=600,resizable=yes,scrollbars=yes')">
                Découvrir la formation
            </button>
        </div>
    <?php endforeach; ?>
</section>


<section id="projects">
    <h2>Projets</h2>
    <?php foreach ($projects as $proj): ?>
        <div class="card">
            <h3><?= htmlspecialchars($proj['titre']) ?></h3>
            <p><?= htmlspecialchars($proj['description']) ?></p>
        </div>
    <?php endforeach; ?>
	</section>
	<section id="contact">
    <h2>Contact</h2>
    <div class="card">
        <p style="font-weight: bold; margin-bottom: 1rem;">Pour me contacter :</p>
        <form id="contactForm" action="contact.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="nom" placeholder="Votre nom" required>
            <input type="email" name="email" placeholder="Votre email" required>
            <input type="text" name="entreprise" placeholder="Votre entreprise" required>
            <textarea name="message" placeholder="Votre message" required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        <div id="message">Message envoyé avec succès !</div>
    </div>
	</section>
<section id="usefull-links">
  <h3>Mes liens utiles</h3>

  <div class="badges-wrapper">
    <?php if (!empty($settings['linkedin'])): ?>
      <div class="linkedin-badge-wrapper">
        <div class="badge-base LI-profile-badge"
            data-locale="fr_FR"
            data-size="large"
            data-theme="dark"
            data-type="HORIZONTAL"
            data-vanity="<?= htmlspecialchars($settings['linkedin']) ?>"
            data-version="v1">
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($settings['github'])): ?>
      <div class="github-badge">
        <div class="github-header">
          <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub" class="github-logo">
          <span>GitHub</span>
        </div>
        <div class="github-content" id="github-profile">
          Chargement...
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<footer>
    <p>
    <?php if (!empty($settings['location'])): ?>
        📍 <?= htmlspecialchars($settings['location']) ?> |
    <?php endif; ?>

    <?php if (!empty($settings['email'])): ?>
        📧 <a href="mailto:<?= htmlspecialchars($settings['email']) ?>" style="color:#3b82f6;"><?= htmlspecialchars($settings['email']) ?></a> |
    <?php endif; ?>

    <?php if (!empty($settings['phone'])): ?>
        ☎ <?= htmlspecialchars($settings['phone']) ?>
    <?php endif; ?>
    </p>

    <?php if (!empty($settings['linkedin'])): ?>
        <p><a href="https://linkedin.com/in/<?= htmlspecialchars($settings['linkedin']) ?>" target="_blank" style="color:#3b82f6;">LinkedIn</a></p>
    <?php endif; ?>
</footer>
<script>
    const phrases = <?= json_encode(explode(',', $settings['keywords'])) ?>;
</script>
<script src="https://platform.linkedin.com/badges/js/profile.js" async defer type="text/javascript"></script>
<script>
    const username = "<?= htmlspecialchars($settings['github']) ?>";


</script> 
<script defer src="assets/js/script.js"></script>
    
</body>
</html>