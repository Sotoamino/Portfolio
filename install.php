<?php
// Fichier : install.php

// Fonction pour écrire dans le fichier log
function logMessage($msg) {
    file_put_contents(__DIR__ . '/install.log', $msg . "\n", FILE_APPEND);
}

// Si la requête est AJAX pour récupérer le log, on renvoie le contenu du log et on stoppe ici
if (isset($_GET['fetchLog']) && $_GET['fetchLog'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    if (file_exists(__DIR__ . '/install.log')) {
        echo file_get_contents(__DIR__ . '/install.log');
    }
    exit;
}

// Déclaration variables
$install_success = false;
$install_path = dirname(__FILE__);
$show_db_action_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage log à chaque installation nouvelle
    file_put_contents(__DIR__ . '/install.log', ""); 

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
        logMessage("[INFO] Tentative de connexion à MySQL...");
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        logMessage("[OK] Connexion à MySQL réussie.");

        if ($step === 'check') {
            $statement = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db_name));
            $db_exists = $statement->fetchColumn();
            if ($db_exists) {
                logMessage("[WARN] La base '$db_name' existe déjà.");
                $show_db_action_form = true;
            } else {
                logMessage("[INFO] Création de la base '$db_name'.");
                $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $_POST['step'] = 'configure';
                $step = 'configure';
            }
        }

        if ($step === 'configure' || $step === 'install') {
            $pdo->exec("USE `$db_name`");
            $db_existing_action = $_POST['db_existing_action'] ?? '';

            if ($db_existing_action === 'cancel') {
                logMessage("[ERROR] Installation annulée par l'utilisateur.");
                exit;
            } elseif ($db_existing_action === 'keep') {
                logMessage("[WARN] Conserver la base existante, mise à jour des données.");
                $pdo->exec("USE `$db_name`");
                $pdo->exec("DELETE FROM settings WHERE id = 1");
                $stmt = $pdo->prepare("INSERT INTO settings (id, first_name, last_name, email, phone, licence_key) VALUES (1, :first_name, :last_name, :email, :phone, :licence_key)");
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':licence_key' => $licence_key,
                ]);
                logMessage("[OK] Données mises à jour dans la base existante.");
            } else {
                if ($db_existing_action == 'drop') {
                    logMessage("[WARN] Suppression de la base existante.");
                    $pdo->exec("DROP DATABASE `$db_name`");
                    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                    $pdo->exec("USE `$db_name`");
                }

                // Création tables
                logMessage("[INFO] Création des tables...");
                $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                    id int NOT NULL AUTO_INCREMENT,
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

                // Ajoute les autres tables (competences, experiences, formations, langues, projets)
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

                // Insérer données dans settings
                $stmt = $pdo->prepare("INSERT INTO settings (first_name, last_name, email, phone, licence_key) VALUES (:first_name, :last_name, :email, :phone, :licence_key)");
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':licence_key', $licence_key);
                $stmt->execute();

                logMessage("[OK] Base de données créée et configurée.");
            }
        }

        // Si la base existe déjà, afficher formulaire d’action et ne pas continuer (ce formulaire sera affiché en HTML)
        if ($show_db_action_form) {
            // On sort du PHP pour afficher formulaire en HTML plus bas
        } else {
            // Suite : copie archive GitHub

            logMessage("[INFO] Début de la copie des fichiers.");

            // Fonctions internes
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

            // Téléchargement archive
            $zipContent = @file_get_contents($zipUrl);
            if ($zipContent === false) {
                logMessage("[ERROR] Impossible de télécharger l'archive GitHub.");
                die("Erreur téléchargement archive.");
            }
            file_put_contents($zipFile, $zipContent);
            logMessage("[OK] Archive téléchargée.");

            // Extraction
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $extractPath = $install_path . '/Portfolio-main';

                // Supprime le dossier s'il existe déjà
                rrmdir($extractPath);
                mkdir($extractPath, 0755, true);

                // Liste des fichiers à ignorer (exact ou par motif)
                $excludedFiles = [
                    'Portfolio-main/install.php',
                    'Portfolio-main/.gitignore',
                    'Portfolio-main/LICENSE',
                    'Portfolio-main/LICENSE.txt'
                ];

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Ignore les fichiers listés
                    if (in_array($filename, $excludedFiles)) {
                        continue;
                    }

                    // Crée le chemin de destination
                    $destination = $install_path . '/' . $filename;

                    // Si c’est un dossier, on le crée
                    if (substr($filename, -1) === '/') {
                        if (!is_dir($destination)) {
                            mkdir($destination, 0755, true);
                        }
                    } else {
                        // Crée les dossiers parents si besoin
                        $parentDir = dirname($destination);
                        if (!is_dir($parentDir)) {
                            mkdir($parentDir, 0755, true);
                        }

                        // Extrait le fichier en copiant depuis l’archive
                        copy("zip://{$zipFile}#{$filename}", $destination);
                    }
                }

                $zip->close();
                logMessage("[OK] Archive extraite avec exclusions.");
            } else {
                logMessage("[ERROR] Extraction échouée.");
                die("Erreur extraction archive.");
            }

            $install_success = true;

        }

    } catch (PDOException $e) {
        logMessage("[ERROR] " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Installation Portfolio</title>
<style>
body { font-family: 'Segoe UI', sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9; }
label { display: block; margin-top: 15px; font-weight: bold; }
input[type=text], input[type=password], input[type=email] { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
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
#container {display: flex;align-items: center;gap: 0.1rem;font-weight: bold;color: #222;}
#m {color:#007BFF;}
#yf {color : #555555;}
#m, #yf span {display: inline-block;font-weight: bold;font-size: 2.5rem;user-select: none;}
#yf {opacity: 0;position: relative;left: -60px; /* place YFOLIO caché à gauche, derrière M */pointer-events: none;}

.log-container { border: 1px solid #ccc; background: #f9f9f9; padding: 15px; height: 250px; overflow-y: scroll; white-space: pre-wrap; font-family: monospace; display:block; margin-top: 20px; }
.log-info { color: green; }
.log-warn { color: orange; }
.log-error { color: red; }
</style>
</head>
<body>

<div id="container">
            <span id="m">M</span>
            <div id="yf">
                <span>y</span><span>F</span><span>o</span><span>l</span><span>i</span><span>o</span>
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

<div id="log" class="log-container">Log d'installation en attente...</div>

<?php if ($install_success): ?>
    <p style="color: green; font-weight: bold;">Installation terminée avec succès !</p>
<?php elseif ($show_db_action_form): ?>
    <form method="post">
        <input type="hidden" name="step" value="configure">
        <input type="hidden" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>">
        <input type="hidden" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>">
        <input type="hidden" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>">
        <input type="hidden" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
        <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
        <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
        <input type="hidden" name="licence_key" value="<?php echo htmlspecialchars($licence_key); ?>">

        <p>La base de données <strong><?php echo htmlspecialchars($db_name); ?></strong> existe déjà.</p>
        <p>Que voulez-vous faire ?</p>

        <label><input type="radio" name="db_existing_action" value="keep" checked> Conserver la base et mettre à jour les données</label><br>
        <label><input type="radio" name="db_existing_action" value="drop"> Supprimer la base et tout recréer</label><br>
        <label><input type="radio" name="db_existing_action" value="cancel"> Annuler l'installation</label><br>

        <button type="submit">Valider</button>
    </form>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="step" value="check">

        <label>Hôte MySQL : <input type="text" name="db_host" value="localhost" required></label>
        <label>Nom de la base : <input type="text" name="db_name" required></label>
        <label>Utilisateur MySQL : <input type="text" name="db_user" required></label>
        <label>Mot de passe : <input type="password" name="db_pass"></label><br><br>

        <label>Prénom : <input type="text" name="first_name" required></label>
        <label>Nom : <input type="text" name="last_name" required></label>
        <label>Email : <input type="email" name="email" required></label>
        <label>Téléphone : <input type="text" name="phone"></label>
        <label>Clé licence : <input type="text" name="licence_key" required></label>

        <button type="submit">Installer</button>
    </form>
<?php endif; ?>

<script>
const logContainer = document.getElementById('log');

function colorizeLog(text) {
    return text
        .replace(/\[OK\]/g, '<span class="log-info">[OK]</span>')
        .replace(/\[INFO\]/g, '<span class="log-info">[INFO]</span>')
        .replace(/\[WARN\]/g, '<span class="log-warn">[WARN]</span>')
        .replace(/\[ERROR\]/g, '<span class="log-error">[ERROR]</span>')
        .replace(/\n/g, '<br>');
}

async function fetchLog() {
    try {
        const res = await fetch('?fetchLog=1', { cache: 'no-store' });
        if (!res.ok) throw new Error('Erreur réseau');

        const text = await res.text();
        const trimmedText = text.trim();

        if (trimmedText.length === 0) {
            logContainer.style.display = 'none'; // cacher la div
        } else {
            logContainer.style.display = 'block'; // afficher si elle était cachée
            logContainer.innerHTML = colorizeLog(trimmedText);
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    } catch (e) {
        console.error('Erreur lors de la récupération du log:', e);
        logContainer.style.display = 'none'; // cacher en cas d'erreur
    }
}

// Actualiser le log toutes les 2 secondes
setInterval(fetchLog, 2000);
fetchLog();
</script>

</body>
</html>