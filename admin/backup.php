<?php
if (!is_admin()) {
    header('Location: /index.php'); // Redirect non-admins
    exit;
}

// === BASE PATHS ===
$baseDir = __DIR__ . '/../'; // project root
$excludeDir = $baseDir . 'uploads/gallery/originals/'; // folder to skip
$dataDir = $baseDir . 'data/'; // backup storage folder

// Ensure /data exists
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0777, true)) {
        die("Error: Cannot create data directory at " . htmlspecialchars($dataDir));
    }
}

// ZIP filename (always overwrite latest backup)
$zipName = 'backup_latest.zip';
$zipPath = $dataDir . $zipName;

// Delete old backup if exists
if (file_exists($zipPath)) unlink($zipPath);

// === CREATE ZIP ===
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Failed to create archive at: " . htmlspecialchars($zipPath));
}

// Recursive function to add files/folders to ZIP
function addFolderToZip($folder, $zip, $basePathLength, $excludeDirPath) {
    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $folder . $item;
        $realFullPath = realpath($fullPath);
        if (!$realFullPath) continue;

        // Skip excluded directory
        if (strpos(str_replace('\\','/',$realFullPath) . '/', str_replace('\\','/',$excludeDirPath)) === 0) continue;

        if (is_dir($fullPath)) {
            addFolderToZip($fullPath . '/', $zip, $basePathLength, $excludeDirPath);
        } elseif (is_file($fullPath)) {
            $localPath = substr($fullPath, $basePathLength);
            $localPath = ltrim(str_replace('\\','/',$localPath), '/');
            $zip->addFile($fullPath, $localPath);
        }
    }
}

// Add files to ZIP
addFolderToZip($baseDir, $zip, strlen($baseDir), $excludeDir);
$zip->close();

// === OUTPUT FOR DOWNLOAD ===
if (file_exists($zipPath) && filesize($zipPath) > 0) {
    if (ob_get_level() > 0) ob_end_clean();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));

    readfile($zipPath); // Stream to browser
    exit; // Keep latest backup on server
} else {
    echo "Error creating ZIP file. Check write permissions for: " . htmlspecialchars($dataDir);
}
