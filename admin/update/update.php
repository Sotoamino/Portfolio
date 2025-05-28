<?php
$repoOwner = 'Sotoamino';
$repoName = 'Portfolio';
$updateDir = __DIR__ . '/latest_release';
$backupDir = __DIR__ . '/backups';
$siteRoot = realpath(__DIR__ . '/../../');

echo "<pre>";
echo "📦 Dossier du site : $siteRoot\n";

// Crée les dossiers si nécessaire
if (!is_dir($updateDir)) {
    mkdir($updateDir, 0777, true);
    echo "📁 Dossier 'latest_release' créé.\n";
}
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
    echo "📁 Dossier 'backups' créé.\n";
}

// 1. Création d’un backup
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
        if (strpos($relativePath, '.git') === 0) continue;
        if (strpos($relativePath, 'install.php') === 0) continue;
        if (strpos($relativePath, 'LICENSE') === 0) continue;
        if (strpos($relativePath, 'Readme.md') === 0) continue;



        if ($file->isDir()) $zip->addEmptyDir($relativePath);
        else $zip->addFile($filePath, $relativePath);
    }

    $zip->close();
    echo "✅ Backup créé : $zipFile\n";
} else {
    exit("❌ Erreur lors de la création du zip de backup.");
}

// 2. Téléchargement de la release
echo "🔽 Récupération de la dernière release GitHub...\n";
$json = file_get_contents("https://api.github.com/repos/$repoOwner/$repoName/releases/latest", false, stream_context_create([
    'http' => [
        'user_agent' => 'PHP'
    ]
]));
$release = json_decode($json, true);
$zipUrl = $release['zipball_url'];
$tmpZipPath = "$updateDir/release.zip";

file_put_contents($tmpZipPath, fopen($zipUrl, 'r', false, stream_context_create(['http'=>['user_agent'=>'PHP']])));

echo "📥 Release téléchargée : $tmpZipPath\n";

// 3. Extraction et copie
$zip = new ZipArchive();
if ($zip->open($tmpZipPath) === TRUE) {
    $zip->extractTo($updateDir);
    $zip->close();
    echo "🗂️ Archive extraite.\n";

    $extractedFolder = glob("$updateDir/{$repoOwner}-{$repoName}-*")[0];
    echo "📂 Dossier extrait : $extractedFolder\n";

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
    echo "🚀 Copie des fichiers...\n";
    copyFolder($extractedFolder, $siteRoot);

    // Sauvegarde de la version
    file_put_contents(__DIR__ . '/.version', $release['tag_name']);
    echo "📌 Version mise à jour : {$release['tag_name']}\n";

    // Nettoyage
    function deleteFolder($path) {
        if (!is_dir($path)) return;
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                deleteFolder($itemPath);
            } else {
                unlink($itemPath);
            }
        }
        rmdir($path);
    }
    deleteFolder($extractedFolder);
    unlink($tmpZipPath);
    echo "🧹 Dossier temporaire nettoyé.\n";
    echo "✅ Mise à jour réussie.\n";
} else {
    echo "❌ Impossible d'extraire la mise à jour.\n";
}
echo "</pre>";
echo '<p><a href="../index.php">← Retourner sur le back office</a></p>';
?>