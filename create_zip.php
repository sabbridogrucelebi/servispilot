<?php
$zipFileName = 'C:/Users/SABRİ DOGRU/Desktop/servispilot_deployment.zip';
$sourceDir = __DIR__;

if (file_exists($zipFileName)) {
    unlink($zipFileName);
}

$zip = new ZipArchive();
if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create zip file.\n");
}

$excludes = [
    '.git',
    'node_modules',
    'mobile-app',
    'tests',
    'servispilot_deployment.zip',
    'test_endpoints.php',
    'test_reports.php',
    '.env' // We exclude .env so we don't accidentally overwrite cPanel's .env if they update later
];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ($files as $name => $file) {
    $relativePath = substr($name, strlen($sourceDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    $skip = false;
    foreach ($excludes as $exclude) {
        if (strpos($relativePath, $exclude) === 0) {
            $skip = true;
            break;
        }
    }
    
    if (!$skip) {
        $zip->addFile($file->getRealPath(), $relativePath);
        $count++;
    }
}

$zip->close();
echo "Successfully created $zipFileName with $count files.\n";
