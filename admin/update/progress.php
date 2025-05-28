<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function sendProgress($percent, $message = '', $done = false) {
    echo "data: " . json_encode([
        'percent' => $percent,
        'message' => $message,
        'done' => $done
    ]) . "\n\n";
    ob_flush();
    flush();
    usleep(200000); // Pour laisser le temps au front d’afficher
}

$repoOwner = 'Sotoamino';
$repoName = 'Portfolio';
$updateDir = __DIR__ . '/latest_release';
$backupDir = __DIR__ . '/backups';
$siteRoot = realpath(__DIR__ . '/../../');

@mkdir($updateDir, 0777, true);
@mkdir($backupDir, 0777, true);

sendProgress(5, "📦 Préparation...");

// 1. Backup
$timestamp = date('Y-m-d_H-i-s');
$zipFile = "$backupDir/backup_$timestamp.zip";
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    $dir = new RecursiveDirectoryIterator($siteRoot, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($siteRoot) + 1);

        if (str_starts_with($relativePath, 'admin/update/backups')) continue;
        if (str_starts_with($relativePath, '.git')) continue;
        if (str_starts_with($relativePath, 'install.php')) continue;
        if (str_starts_with($relativePath, 'LICENSE')) continue;
        if (str_starts_with($relativePath, 'Readme.md')) continue;

        if ($file->isDir()) $zip->addEmptyDir($relativePath);
        else $zip->addFile($filePath, $relativePath);
    }
    $zip->close();
    sendProgress(20, "✅ Backup créé");
} else {
    sendProgress(100, "❌ Erreur de backup", true);
    exit;
}

// 2. Téléchargement
$json = file_get_contents("https://api.github.com/repos/$repoOwner/$repoName/releases/latest", false, stream_context_create([
    'http' => ['user_agent' => 'PHP']
]));
$release = json_decode($json, true);
$zipUrl = $release['zipball_url'];
$tmpZipPath = "$updateDir/release.zip";
file_put_contents($tmpZipPath, fopen($zipUrl, 'r', false, stream_context_create(['http'=>['user_agent'=>'PHP']])));
sendProgress(35, "📥 Release téléchargée");

// 3. Extraction
$zip = new ZipArchive();
if ($zip->open($tmpZipPath) === TRUE) {
    $zip->extractTo($updateDir);
    $zip->close();
    sendProgress(45, "🗂️ Archive extraite");
    $extractedFolder = glob("$updateDir/{$repoOwner}-{$repoName}-*")[0];
} else {
    sendProgress(100, "❌ Extraction échouée", true);
    exit;
}

// 4. Copie avec progression
function countFiles($dir) {
    $count = 0;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        if ($file->isFile()) $count++;
    }
    return $count;
}

function copyFolderProgress($src, $dst, $totalFiles) {
    static $copied = 0;
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ($file = readdir($dir))) {
        if ($file == '.' || $file == '..') continue;
        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";
        if (is_dir($srcPath)) {
            copyFolderProgress($srcPath, $dstPath, $totalFiles);
        } else {
            copy($srcPath, $dstPath);
            $copied++;
            $percent = 45 + round(($copied / $totalFiles) * 45);
            sendProgress($percent);
        }
    }
    closedir($dir);
}
$totalFiles = countFiles($extractedFolder);
copyFolderProgress($extractedFolder, $siteRoot, $totalFiles);

// 5. Finalisation
file_put_contents(__DIR__ . '/.version', $release['tag_name']);
unlink($tmpZipPath);

function deleteFolder($path) {
    if (!is_dir($path)) return;
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $itemPath = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($itemPath)) deleteFolder($itemPath);
        else unlink($itemPath);
    }
    rmdir($path);
}
deleteFolder($extractedFolder);
sendProgress(100, "✅ Mise à jour vers {$release['tag_name']} terminée", true);
?>
