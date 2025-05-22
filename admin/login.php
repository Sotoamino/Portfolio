<?php
session_start();
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
            $_SESSION['user_id'] = $user['id'];  // Enregistrer l'ID de l'utilisateur en session
            header('Location: index.php');  // Redirection vers la page d'accueil
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
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
