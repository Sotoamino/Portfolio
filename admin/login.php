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
    // Dans le bloc POST, en cas d'échec
    $_SESSION['login_attempts']++;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../tools/sqlconnect.php';

$error = '';

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';


    // Vérification du token CSRF
    if ($csrf !== $_SESSION['csrf_token']) {
        $error = "Requête invalide. Veuillez réessayer.";
        unset($_SESSION['csrf_token']);

    }
    // Vérification que le nom d'utilisateur et le mot de passe ne sont pas vides
    elseif (!empty($username) && !empty($password)) {
        // Préparer la requête pour récupérer les données de l'utilisateur
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification que l'utilisateur existe et que le mot de passe est correct
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); // <- ici
            $_SESSION['user_id'] = $user['id'];
            unset($_SESSION['csrf_token']); // <- aussi ici
            header('Location: index.php');
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
            $_SESSION['login_attempts']++;
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | salamagnon.fr</title>
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
