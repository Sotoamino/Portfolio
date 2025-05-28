<?php
session_start();
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if ($_SESSION['login_attempts'] > 10) {
    $error = "Trop de tentatives. Réessayez plus tard.";
    unset($_SESSION['csrf_token']);
} else {
    $_SESSION['login_attempts']++;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../tools/sqlconnect.php'; // Connexion PDO

$error = '';

// Récupération de la licence
$stmt = $pdo->prepare("SELECT licence_key FROM settings LIMIT 1");
$stmt->execute();
$licenceKey = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if ($csrf !== $_SESSION['csrf_token']) {
        $error = "Requête invalide. Veuillez réessayer.";
        unset($_SESSION['csrf_token']);
    } elseif (!empty($username) && !empty($password)) {
        // Hash du mot de passe (ex: SHA256) - adapter selon ce que l’API attend
        $passwordHash = hash('sha256', $password);

        // Préparation des données à envoyer à l'API
        $postData = [
            'licence_key' => $licenceKey,
            'username' => $username,
            'password_hash' => $passwordHash,
        ];

        // Appel API user/validate.php
        $ch = curl_init('https://tools.salamagnon.fr/api/user/validate.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['user_id'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $data['user_id'];
                unset($_SESSION['csrf_token']);
                header('Location: index.php');
                exit;
            } else {
                $error = $data['message'] ?? "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } else {
            $error = "Erreur de communication avec le serveur d’authentification.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!-- Ensuite le même HTML (formulaire) -->

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
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <button type="submit">Se connecter</button>
        </form>
    </div>
    <script src="assets/js/login.js"></script>
</body>
</html>
