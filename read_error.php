<?php
$log = file_get_contents(__DIR__ . '/storage/logs/laravel.log');
$lines = explode("\n", $log);
$lastErrorIndex = 0;
foreach ($lines as $i => $l) {
    if (str_contains($l, 'local.ERROR:')) {
        $lastErrorIndex = $i;
    }
}
$slice = array_slice($lines, $lastErrorIndex, 20);
echo implode("\n", $slice);
