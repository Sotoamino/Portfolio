<?php
$install_success = false;
$admin_password = '';
$install_path = dirname(__FILE__); // Installation forcée ici

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';

    $errors = [];
    if (!$db_name || !$db_user || !$first_name || !$last_name || !$email) {
        $errors[] = "Merci de remplir tous les champs obligatoires.";
    }

    if (!empty($errors)) {
        foreach ($errors as $e) echo "<p style='color:red;'>$e</p>";
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$db_name`");

            function generateRandomPassword($length = 12) {
                return bin2hex(random_bytes($length / 2));
            }

            $pdo->exec("DROP TABLE IF EXISTS `settings`");
            $pdo->exec("CREATE TABLE `settings` ( `id` int NOT NULL, `first_name` varchar(100), `last_name` varchar(100), `email` varchar(150), `phone` varchar(30), PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
            $stmt = $pdo->prepare("INSERT INTO `settings` (`id`, `first_name`, `last_name`, `email`, `phone`) VALUES (1, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone]);

            // Autres tables réduites pour clarté
            $pdo->exec("DROP TABLE IF EXISTS `users`");
            $pdo->exec("CREATE TABLE `users` ( `id` int NOT NULL AUTO_INCREMENT, `username` varchar(100), `password_hash` varchar(255), `email` varchar(255), PRIMARY KEY (`id`), UNIQUE(`username`), UNIQUE(`email`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

            $install_success = true;
            if (empty($admin_password)) {
                $admin_password = generateRandomPassword();
            }
            $admin_password_hash = password_hash($admin_password, PASSWORD_BCRYPT);

            $stmtAdmin = $pdo->prepare("INSERT INTO users (id, username, password_hash, email) VALUES (1, ?, ?, ?)");
            $stmtAdmin->execute(['admin', $admin_password_hash, $email]);

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
<?php if (!$install_success): ?>
<form method="post">
    <h2>Configuration base de données</h2>
    <label>Hôte MySQL
        <input type="text" name="db_host" value="localhost" required />
    </label>
    <label>Nom de la base de données
        <input type="text" name="db_name" required />
    </label>
    <label>Utilisateur MySQL
        <input type="text" name="db_user" required />
    </label>
    <label>Mot de passe MySQL
        <input type="password" name="db_pass" />
    </label>
    <h2>Informations administrateur</h2>
    <label>Prénom
        <input type="text" name="first_name" required />
    </label>
    <label>Nom
        <input type="text" name="last_name" required />
    </label>
    <label>Email
        <input type="text" name="email" required />
    </label>
    <label>Téléphone
        <input type="text" name="phone" />
    </label>
    <label>Mot de passe admin (laisser vide pour un généré aléatoirement)
        <input type="password" name="admin_password" />
    </label>
    <button type="submit">Installer maintenant</button>
</form>
<?php else: ?>
<h2 class="success">Installation terminée avec succès !</h2>
<p><strong>Identifiant admin :</strong> admin</p>
<p><strong>Mot de passe admin :</strong> <code><?= htmlspecialchars($admin_password) ?></code></p>
<p>Vous pouvez maintenant accéder à <code>/admin/</code></p>
<?= $unlink_message ?? '' ?>
<?php endif; ?>
</body>
</html>
