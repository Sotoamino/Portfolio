<?php
$install_success = false;
$admin_password = '';
$install_path = dirname(__FILE__);
$show_db_action_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'check';

    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $licence_key = trim($_POST['licence_key'] ?? '');

    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        if ($step === 'check') {
            echo "<p style='color:green;'>Connexion &agrave; MySQL r&eacute;ussie.</p>";
            $statement = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db_name));
            $db_exists = $statement->fetchColumn();
            if ($db_exists) {
                $show_db_action_form = true;
            } else {
                $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                echo "<p style='color:green;'>La base <strong>$db_name</strong> a &eacute;t&eacute; cr&eacute;&eacute;e automatiquement.</p>";
                $_POST['step'] = 'configure';
                $step = 'configure';
            }
        }

        if ($step === 'configure' || $step === 'install') {
            echo "<p style='color:green;'>Configuration de la base de donn&eacute;es.</p>";
            $pdo->exec("USE `$db_name`");
            $db_existing_action = $_POST['db_existing_action'] ?? '';
            $statement = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db_name));
            $db_exists = $statement->fetchColumn();

            if ($db_exists) {
                if ($db_existing_action === 'cancel') {
                    echo "<p style='color:red;'>Installation annul&eacute;e. La base <strong>$db_name</strong> existe d&eacute;j&agrave;.</p>";
                    exit;
                } elseif ($db_existing_action === 'keep') {

                }
                else  {
                    $pdo->exec("DROP DATABASE `$db_name`");
                    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                    $pdo->exec("USE `$db_name`");
                    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                        id int NOT NULL,
                        first_name varchar(100),
                        last_name varchar(100),
                        email varchar(150),
                        phone varchar(30),
                        location varchar(100),
                        linkedin varchar(100),
                        linkedin_status tinyint(1) NOT NULL DEFAULT 0,
                        instagram varchar(100),
                        instagram_status tinyint(1) NOT NULL DEFAULT 0,
                        github varchar(100),
                        github_status tinyint(1) NOT NULL DEFAULT 0,
                        twitter varchar(100),
                        twitter_status tinyint(1) NOT NULL DEFAULT 0,
                        discord varchar(100),
                        discord_status tinyint(1) NOT NULL DEFAULT 0,
                        keywords text,
                        contact_api_key varchar(255),
                        contact_from varchar(255),
                        contact_to varchar(255),
                        maintenance_status tinyint(1) NOT NULL DEFAULT 0,
                        particle_config VARCHAR(255) DEFAULT 'default.json',
                        theme VARCHAR(255) DEFAULT 'blue',
                        licence_key VARCHAR(255),
                        PRIMARY KEY (id)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

                    $pdo->exec("CREATE TABLE IF NOT EXISTS competences (
                        id int NOT NULL AUTO_INCREMENT,
                        nom varchar(100) NOT NULL,
                        niveau int NOT NULL,
                        ordre int DEFAULT 0,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

                    $pdo->exec("CREATE TABLE IF NOT EXISTS experiences (
                        id int NOT NULL AUTO_INCREMENT,
                        titre varchar(255) NOT NULL,
                        entreprise varchar(255) NOT NULL,
                        content text DEFAULT NULL,
                        tags varchar(255) DEFAULT NULL,
                        startDate date NOT NULL,
                        endDate date NOT NULL,
                        ordre int DEFAULT 0,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

                    $pdo->exec("CREATE TABLE IF NOT EXISTS formations (
                        id int NOT NULL AUTO_INCREMENT,
                        name varchar(255) NOT NULL,
                        titre varchar(255) NOT NULL,
                        startDate date NOT NULL,
                        endDate date NOT NULL,
                        ordre int NOT NULL,
                        link varchar(255) NOT NULL,
                        description text NOT NULL,
                        PRIMARY KEY (id)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

                    $pdo->exec("CREATE TABLE IF NOT EXISTS langues (
                        id int NOT NULL AUTO_INCREMENT,
                        nom varchar(100) NOT NULL,
                        niveau int NOT NULL,
                        ordre int DEFAULT 0,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

                    $pdo->exec("CREATE TABLE IF NOT EXISTS projets (
                        id int NOT NULL AUTO_INCREMENT,
                        titre varchar(255) NOT NULL,
                        description text DEFAULT NULL,
                        link text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
                        ordre int DEFAULT 0,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

                    echo "<p style='color:green;'>La base a &eacute;t&eacute; configur&eacute;e avec succ&egrave;s.</p>";
                } else {
                    echo "<p style='color:red;'>Action invalide sur la base existante.</p>";
                    exit;
                }
            }       
            function recurse_copy($src, $dst) {
                $dir = opendir($src);
                if (!is_dir($dst)) mkdir($dst, 0755, true);
                while(false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..')) {
                        if (is_dir("$src/$file")) recurse_copy("$src/$file", "$dst/$file");
                        else copy("$src/$file", "$dst/$file");
                    }
                }
                closedir($dir);
            }

            function rrmdir($dir) {
                if (!is_dir($dir)) return;
                foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
                    (is_dir("$dir/$file")) ? rrmdir("$dir/$file") : unlink("$dir/$file");
                }
                rmdir($dir);
            }

            $zipUrl = 'https://github.com/Sotoamino/Portfolio/archive/refs/heads/main.zip';
            $zipFile = sys_get_temp_dir() . '/repo.zip';
            file_put_contents($zipFile, file_get_contents($zipUrl));
            $zip = new ZipArchive();
            if ($zip->open($zipFile) === TRUE) {
                $tempExtract = sys_get_temp_dir() . '/repo_extract_' . uniqid();
                mkdir($tempExtract);
                $zip->extractTo($tempExtract);
                $zip->close();
                $subdirs = glob($tempExtract . '/*', GLOB_ONLYDIR);
                if ($subdirs) recurse_copy($subdirs[0], $install_path);
                rrmdir($tempExtract);
            }
            unlink($zipFile);

            $configContent = "<?php\n" .
                "\$host = " . var_export($db_host, true) . ";\n" .
                "\$db = " . var_export($db_name, true) . ";\n" .
                "\$user = " . var_export($db_user, true) . ";\n" .
                "\$pass = " . var_export($db_pass, true) . ";\n" .
                "?>\n";

            file_put_contents($install_path . '/tools/options.php', $configContent);

            // Tentative suppression du fichier install.php
            $install_file = __FILE__;
            $unlink_message = '';
            if (!@unlink($install_file)) {
                $unlink_message = "<p style='color:red;'>Suppression automatique de install.php impossible. Merci de le supprimer manuellement.</p>";
            }
            echo "<p class='success'>Installation réussie !</p>";
            if ($unlink_message) {
                echo $unlink_message;
            } else {
                echo "<p>Le fichier <code>install.php</code> a été supprimé.</p>";
            }
            $install_success = true;
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Erreur lors de la configuration : ". htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation du Portfolio</title>
<meta name="description" content="Installation du Portfolio - Configuration de la base de données et des informations administratives.">
<meta name="keywords" content="installation, portfolio, base de données, configuration, admin">
<meta name="author" content="Sotoamino">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>

body { font-family: 'Segoe UI', sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9; }
label { display: block; margin-top: 15px; font-weight: bold; }
input[type=text], input[type=password] { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
button { margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
.success { color: green; font-weight: bold; }
h1 { text-align: center; color: #333; }
h2 { color: #555; }
form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
input[type=hidden] { display: none; }
select { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
select:focus { outline: none; border-color: #007bff; } 
h2.success { color: green; text-align: center; }
p { margin-top: 10px; }
p a { color: #007bff; text-decoration: none; }
p a:hover { text-decoration: underline; }
  #container {
    display: flex;
    align-items: center;
    gap: 0.1rem;
    font-weight: bold;
    color: #222;
  }
  #m {
    color:#007BFF;
  }
  #yf {
    color : #555555;
  }
  #m, #yf span {
    display: inline-block;
    font-weight: bold;
    font-size: 2.5rem;
    user-select: none;
  }
  #yf {
    opacity: 0;
    position: relative;
    left: -60px; /* place YFOLIO caché à gauche, derrière M */
    pointer-events: none;
  }
</style>
</head>
<div id="container">
  <span id="m">M</span>
  <div id="yf">
    <span>Y</span><span>F</span><span>O</span><span>L</span><span>I</span><span>O</span>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
<script>
  const m = document.querySelector('#m');
  const yf = document.querySelector('#yf');
  const yfSpans = yf.querySelectorAll('span');

  anime({
    targets: m,
    rotate: '1turn',
    duration: 2000,
    easing: 'easeInOutSine',
    complete: () => {
      anime.timeline()
        .add({
          targets: yf,
          opacity: [0, 1],
          left: ['-60px', '0px'],
          duration: 700,
          easing: 'easeOutCubic',
        })
        .add({
          targets: yfSpans,
          ranslateX: [-30, 0], // glisse de gauche vers droite (depuis -30px)
          opacity: [0, 1],
          delay: anime.stagger(80),
          duration: 400,
          easing: 'easeOutBack',
          offset: '-=400'
        });
    }
  });
</script>
<br/>
<?php if (!$install_success && empty($show_db_action_form)): ?>
<form method="post">
    <input type="hidden" name="step" value="check" />
    <h2>Configuration base de données</h2>
    <label>Hôte MySQL <input type="text" name="db_host" value="localhost" required /></label>
    <label>Nom de la base <input type="text" name="db_name" required /></label>
    <label>Utilisateur MySQL <input type="text" name="db_user" required /></label>
    <label>Mot de passe MySQL <input type="password" name="db_pass" /></label>

    <h2>Infos administrateur</h2>
    <label>Prénom <input type="text" name="first_name" required /></label>
    <label>Nom <input type="text" name="last_name" required /></label>
    <label>Email <input type="text" name="email" required /></label>
    <label>Téléphone <input type="text" name="phone" /></label>
    <label>Clé de licence <input type="text" name="licence_key" /></label>
    <button type="submit">Vérifier la base</button>
</form>
<?php elseif ($show_db_action_form): ?>
<form method="post">
    <input type="hidden" name="step" value="install" />
    <?php foreach ($_POST as $key => $value):
        if (!in_array($key, ['step', 'db_existing_action'])):
            $escaped = htmlspecialchars($value);
            echo "<input type='hidden' name='$key' value='$escaped' />";
        endif;
    endforeach; ?>

    <h2>La base "<?= htmlspecialchars($_POST['db_name']) ?>" existe déjà</h2>
    <label>Action à effectuer :
        <select name="db_existing_action" required>
            <option value="">-- Choisissez --</option>
            <option value="drop">Supprimer la base existante</option>
            <option value="keep">Conserver la base existante</option>
            <option value="cancel">Annuler l'installation</option>
        </select>
    </label>
    <button type="submit">Continuer</button>
</form>
<?php else: ?>
<h2 class="success">Installation terminée avec succès !</h2>
<p>Vous pouvez maintenant accéder à <a href='./admin/'><code>/admin/</code></a></p>
<?php endif; ?>
</body>
</html>
