<?php
$backupDir = __DIR__ . '/backups';
$siteRoot = realpath(__DIR__ . '/../../');

$backups = glob("$backupDir/backup_*.zip");
rsort($backups);

if (empty($backups)) {
    exit("❌ Aucun backup trouvé.");
}

$latestBackup = $backups[0];
$zip = new ZipArchive();
if ($zip->open($latestBackup) === TRUE) {
    $zip->extractTo($siteRoot);
    $zip->close();
    echo "✅ Restauration effectuée depuis $latestBackup";
} else {
    echo "❌ Erreur lors de l’ouverture du zip.";
}
