<?php
require './tools/sqlconnect.php';

$settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

// contact.php

// ⚡ Ta clé API SendGrid
$apiKey = $settings['contact_api_key']; 

// ⚡ Adresse expéditeur (ton adresse)
$from = $settings['contact_from'];

// ⚡ Adresse destinataire (où tu veux recevoir le mail)
$destinataire = $settings['contact_to'];

// Démarrer la session pour utiliser le token CSRF
session_start();

// Vérifier le token CSRF
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = html_entity_decode($_POST['nom'], ENT_QUOTES, 'UTF-8');
    $entreprise = html_entity_decode($_POST['entreprise'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $message = html_entity_decode($_POST['message'], ENT_QUOTES, 'UTF-8');

    if ($nom && $email && $message) {
        // Construire le sujet et le corps
        $subject = "Nouveau message de $nom";
        $body = "Vous avez reçu un nouveau message depuis le formulaire de contact :\n\n"
      . "Nom : $nom\n"
      . "Email : $email\n"
      . "Entreprise : $entreprise\n"
      . "Message :\n$message\n";

        // URL API SendGrid
        $url = 'https://api.sendgrid.com/v3/mail/send';

        // Préparer les données pour l'API
        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $destinataire]],
                    'subject' => $subject,
                ]
            ],
            'from' => ['email' => $from],
            'reply_to' => ['email' => $email],
            'content' => [
                [
                    'type' => 'text/plain',
                    'value' => $body,
                ]
            ]
        ];

        $data_json = json_encode($data);

        // Envoyer via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) || $http_code >= 400) {
            echo 'error: Impossible d\'envoyer le message';
        } else {
            echo 'success';
        }
        curl_close($ch);

    } else {
        echo 'error: Données invalides';
    }
}
?>
