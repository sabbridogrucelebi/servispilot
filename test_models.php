<?php
$d = new RecursiveDirectoryIterator('app/Models');
$i = new RecursiveIteratorIterator($d);
foreach ($i as $f) {
    if ($f->isFile() && $f->getExtension() == 'php') {
        $c = file_get_contents($f->getPathname());
        if (preg_match('/class\s+\w+\s+extends\s+Model/', $c) && !str_contains($c, 'BelongsToCompany')) {
            echo $f->getPathname() . "\n";
        }
    }
}
