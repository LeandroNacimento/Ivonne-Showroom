<?php
$log = file_get_contents(__DIR__ . '/storage/logs/test_catalog.log');
// Use regex to scoop the actual Exception text
preg_match('/.*Exception.*/i', $log, $matches);
if (isset($matches[0])) {
    echo $matches[0] . "\n";
} else {
    // just scoop the whole thing but without weird characters
    echo mb_convert_encoding($log, 'UTF-8', 'auto');
}
