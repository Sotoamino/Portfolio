<?php
$repoOwner = 'Sotoamino';
$repoName = 'Portfolio';
$updateDir = __DIR__ . '/latest_release';
if (!is_dir($updateDir)) {
    mkdir($updateDir, 0777, true); // Crée le dossier récursivement si nécessaire
}
$backupDir = __DIR__ . '/backups';
$siteRoot = realpath(__DIR__ . '/../../');

// 1. Créer dossier de backup zippé
$timestamp = date('Y-m-d_H-i-s');
$zipFile = "$backupDir/backup_$timestamp.zip";

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $dir = new RecursiveDirectoryIterator($siteRoot, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($siteRoot) + 1);

        if (strpos($relativePath, 'admin/update/backups') === 0) continue;
        if (strpos($relativePath, '.git') === 0) {
            continue; // Ignore les fichiers/dossiers .git
        }
        if ($file->isDir()) $zip->addEmptyDir($relativePath);
        else $zip->addFile($filePath, $relativePath);
    }

    $zip->close();
} else {
    exit("❌ Erreur lors de la création du zip de backup.");
}

// 2. Télécharger dernière release
$json = file_get_contents("https://api.github.com/repos/$repoOwner/$repoName/releases/latest", false, stream_context_create([
    'http' => [
        'user_agent' => 'PHP'
    ]
]));
$release = json_decode($json, true);
$zipUrl = $release['zipball_url'];

$tmpZipPath = "$updateDir/release.zip";
file_put_contents($tmpZipPath, fopen($zipUrl, 'r', false, stream_context_create(['http'=>['user_agent'=>'PHP']])));

// 3. Extraire et copier fichiers
$zip = new ZipArchive();
if ($zip->open($tmpZipPath) === TRUE) {
    $zip->extractTo($updateDir);
    $zip->close();

    $extractedFolder = glob("$updateDir/{$repoOwner}-{$repoName}-*")[0];
    
    function copyFolder($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = "$src/$file";
                $dstPath = "$dst/$file";
                if (is_dir($srcPath)) {
                    copyFolder($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
        closedir($dir);
    }

    copyFolder($extractedFolder, $siteRoot);
    file_put_contents(__DIR__ . '/.version', $release['tag_name']);
    echo "✅ Mise à jour réussie.";
} else {
    echo "❌ Impossible d'extraire la mise à jour.";
}
