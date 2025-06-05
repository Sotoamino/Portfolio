<?php
session_start();
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self';");

require_once '../tools/sqlconnect.php'; // Connexion PDO
$error = '';

$columnCheck = $pdo->query("SHOW COLUMNS FROM settings LIKE 'licence_key'")->fetch(PDO::FETCH_ASSOC);
if (!$columnCheck) {
    $pdo->exec("ALTER TABLE settings ADD COLUMN licence_key VARCHAR(255) DEFAULT NULL");
}


if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if ($_SESSION['login_attempts'] > 10 && $_POST['action'] === 'login') {
    $error = "Trop de tentatives. Réessayez plus tard.";
} else {
    $_SESSION['login_attempts']++;
}





// Récupération de la licence
$stmt = $pdo->prepare("SELECT licence_key FROM settings LIMIT 1");
$stmt->execute();
$licenceKey = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || $csrf !== $_SESSION['csrf_token']) {
                $error = "Requête invalide. Veuillez réessayer.";
            } elseif (!empty($username) && !empty($password)) {
                // Préparation des données à envoyer à l'API
                $postData = [
                    'licence_key' => $licenceKey,
                    'username' => $username,
                    'password' => $password,  // mot de passe clair, l'API fait le hash+vérif
                ];

                // Appel API user/validate.php
                $ch = curl_init('https://tools.salamagnon.fr/api/user/validate.php');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout augmenté

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($response === false) {
                    $curlErr = curl_error($ch);
                }
                curl_close($ch);

                if ($response === false) {
                    $error = "Erreur CURL : $curlErr";
                } elseif ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    if (isset($data['status']) && $data['status'] === 'success' && isset($data['user_id'])) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $data['user_id'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        header('Location: index.php');
                        exit;
                    }
                }
                elseif ($httpCode === 400 && $response) {
                    $error = $data['message'] ?? "Nom d'utilisateur ou mot de passe incorrect.";
                }
                elseif ($httpCode === 401 && $response) {
                    $error = $data['message'] ?? "Nom d'utilisateur ou mot de passe incorrect.";
                }
                elseif ($httpCode === 403 && $response) {
                    $error = $data['message'] ?? "Vous n'êtes pas autorisé à vous connecter.";
                }
                 else {
                    $data = json_decode($response, true);
                    $error = "Erreur de communication avec le serveur d’authentification.";
                    exit;
                }

            } else {
                $error = "Veuillez remplir tous les champs.";
            }
        }
        elseif ($_POST['action'] === 'new-licence') {
            $licence = trim($_POST['licence-key'] ?? '');
            $csrf = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || $csrf !== $_SESSION['csrf_token']) {
                $error = "Requête invalide. Veuillez réessayer.";
            } else if (empty($licence)) {
                $error = "Aucune licence saisie.";
            } else {

                // Appel de l'API de validation
                $ch = curl_init('https://tools.salamagnon.fr/api/licence/validate.php');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'x-api-key: ' . $licence
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $response = curl_exec($ch);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    if (isset($data['success']) && $data['success'] === true) {
                    $stmt = $pdo->prepare("UPDATE settings SET licence_key = :licence LIMIT 1");
                    $stmt->execute(['licence' => $licence]);

                    // Recharge la page pour afficher le formulaire de login
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                    }
                    elseif (isset($data['success']) && $data['success'] === false) {
                        $error = "Licence saisie invalide." . $data['message'];
                    }
                    else {
                        $error = "une erreur est survenue.";
                    }
                } else {
                    $error = "Serveur de licence injoignable HTTP Code : $httpCode";
                    $data = json_decode($response, true);
                }
            }
        }
        else {
            $error = "Action non reconnue.";
        }
    }
}
?>

<!-- Ensuite le même HTML (formulaire) -->
<?php if(isset($licenceKey) && $licenceKey !== '') : ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | Back Office</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        <a style="text-decoration:none;color:black" href="../index.php">Retourner sur le site</a>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="" id="login-form">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required autocomplete="username">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')); ?>">
            <input type="hidden" name="action" value="login" />
            <button type="submit">Se connecter</button>
        </form>
    </div>
    <script src="assets/js/login.js"></script>
</body>
<?php else : ?>
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur | Back Office</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Rentrez une clé de licence</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="" id="login-form">
            <p>Veuillez entrer une clé de licence valide</p>
            <p>Vous pouvez en obtenir une <a href="https://tools.salamagnon.fr" target="_blank" rel="noopener noreferrer">ici</a></p>
            <label for="licence">Clé de licence :</label>
            <input type="text" id="licence-key" name="licence-key" required>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')); ?>">
            <input type="hidden" name="action" value="new-licence" />
            <button type="submit">Valider</button>
        </form>
    </div>
    <script src="assets/js/login.js"></script>
</body>
<?php endif ?>
</html>
