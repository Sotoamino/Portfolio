<?php
$repoOwner = 'Sotoamino';
$repoName = 'Portfolio';
$currentVersionFile = __DIR__ . '/.version'; // Fichier contenant la version locale actuelle

// Récupération de la dernière version sur GitHub
$context = stream_context_create(['http' => ['user_agent' => 'PHP']]);
$data = file_get_contents("https://api.github.com/repos/$repoOwner/$repoName/releases/latest", false, $context);
$release = json_decode($data, true);
$latestVersion = $release['tag_name'] ?? null;

if (!$latestVersion) {
  echo json_encode(['error' => 'Impossible de récupérer la version GitHub.']);
  exit;
}

// Lecture de la version actuelle locale
$currentVersion = file_exists($currentVersionFile) ? trim(file_get_contents($currentVersionFile)) : '0.0.0';

if (version_compare($latestVersion, $currentVersion, '>')) {
  echo json_encode(['update_available' => true, 'latest' => $latestVersion, 'current' => $currentVersion]);
} else {
  echo json_encode(['update_available' => false, 'latest' => $latestVersion, 'current' => $currentVersion]);
}
