<?php
$repo = "Sotoamino/Portfolio"; // Change si nécessaire
$apiUrl = "https://api.github.com/repos/$repo/releases/latest";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Update-Agent');

$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(["error" => "Erreur réseau"]);
    exit;
}

$data = json_decode($response, true);
echo json_encode([
    "tag_name" => $data["tag_name"] ?? null,
    "zipball_url" => $data["zipball_url"] ?? null
]);
