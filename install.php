<?php
$install_success = false;
$admin_password = '';
$install_path = dirname(__FILE__);
$show_db_action_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'check';

    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $licence_key = $_POST['licence_key'] ?? '';

    if ($step === 'check') {
        try {
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $statement = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db_name));
            $db_exists = $statement->fetchColumn();

            if ($db_exists) {
                $show_db_action_form = true;
            } else {
                $_POST['step'] = 'install';
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>Connexion MySQL échouée : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    if ($_POST['step'] === 'install') {
        $db_existing_action = $_POST['db_existing_action'] ?? '';
        try {
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $statement = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db_name));
            $db_exists = $statement->fetchColumn();

            if ($db_exists) {
                if ($db_existing_action === 'cancel') {
                    echo "<p style='color:red;'>Installation annulée. La base <strong>$db_name</strong> existe déjà.</p>";
                    exit;
                } elseif ($db_existing_action === 'drop') {
                    $pdo->exec("DROP DATABASE `$db_name`");
                } elseif ($db_existing_action !== 'keep') {
                    $pdo->exec("DELETE FROM settings WHERE id = 1");
                    $stmt = $pdo->prepare("INSERT INTO settings (id, first_name, last_name, email, phone, licence_key) VALUES (1, ?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $email, $phone, $licence_key]);
                    echo "<p style='color:green;'>La base a été conservé.</p>";
                    exit;
                }
            }

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$db_name`");

            $pdo->exec("DROP TABLE IF EXISTS settings");
            $pdo->exec("CREATE TABLE settings (
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

            $stmt = $pdo->prepare("INSERT INTO settings (id, first_name, last_name, email, phone, licence_key) VALUES (1, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $licence_key]);
$pdo->exec("DROP TABLE IF EXISTS competences");
            $pdo->exec("
                CREATE TABLE competences (
                  id int NOT NULL AUTO_INCREMENT,
                  nom varchar(100) NOT NULL,
                  niveau int NOT NULL,
                  ordre int DEFAULT 0,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ");

            $pdo->exec("DROP TABLE IF EXISTS experiences");
            $pdo->exec("
                CREATE TABLE experiences (
                  id int NOT NULL AUTO_INCREMENT,
                  titre varchar(255) NOT NULL,
                  entreprise varchar(255) NOT NULL,
                  content text DEFAULT NULL,
                  tags varchar(255) DEFAULT NULL,
                  startDate date NOT NULL,
                  endDate date NOT NULL,
                  ordre int DEFAULT 0,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ");

            $pdo->exec("DROP TABLE IF EXISTS formations");
            $pdo->exec("
                CREATE TABLE formations (
                  id int NOT NULL AUTO_INCREMENT,
                  name varchar(255) NOT NULL,
                  titre varchar(255) NOT NULL,
                  startDate date NOT NULL,
                  endDate date NOT NULL,
                  ordre int NOT NULL,
                  link varchar(255) NOT NULL,
                  description text NOT NULL,
                  PRIMARY KEY (id)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");

            $pdo->exec("DROP TABLE IF EXISTS langues");
            $pdo->exec("
                CREATE TABLE langues (
                  id int NOT NULL AUTO_INCREMENT,
                  nom varchar(100) NOT NULL,
                  niveau int NOT NULL,
                  ordre int DEFAULT 0,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ");

            $pdo->exec("DROP TABLE IF EXISTS projets");
            $pdo->exec("
                CREATE TABLE projets (
                  id int NOT NULL AUTO_INCREMENT,
                  titre varchar(255) NOT NULL,
                  description text DEFAULT NULL,
                  link text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
                  ordre int DEFAULT 0,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ");
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

        } catch (Exception $e) {
            echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Installation Portfolio</title>
<style>
body { font-family: 'Segoe UI', sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9; }
label { display: block; margin-top: 15px; font-weight: bold; }
input[type=text], input[type=password] { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
button { margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
.success { color: green; font-weight: bold; }
</style>
</head>
<body>
<h1>Installation du Portfolio</h1>
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
<p>Vous pouvez maintenant accéder à <code>/admin/</code></p>
<?php endif; ?>
</body>
</html>
