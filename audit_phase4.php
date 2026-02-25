<?php

$directories = [
    'app',
    'resources/views',
    'routes',
    'tests'
];

$regexes = [
    'variation_color_read' => '/\$[a-zA-Z_]*variation(?:s)?(->array)?(?:\[\'color\'\]|->color)\b/i',
    'image_color_read' => '/\$[a-zA-Z_]*image(?:s)?(->array)?(?:\[\'color\'\]|->color)\b/i',
    'variation_product_id_read' => '/\$[a-zA-Z_]*variation(?:s)?(?:\[\'product_id\'\]|->product_id)\b/i',
    'image_product_id_read' => '/\$[a-zA-Z_]*image(?:s)?(?:\[\'product_id\'\]|->product_id)\b/i',
    'where_color' => '/(?:->where\(|->orWhere\()\s*[\'"]color[\'"]\s*,/i',
    'where_product_id' => '/(?:->where\(|->orWhere\()\s*[\'"]product_id[\'"]\s*,/i', // Too broad, will need manual review
];

$results = [];

foreach ($directories as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir))
        continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/' . $dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $index => $line) {
                foreach ($regexes as $key => $regex) {
                    if (preg_match($regex, $line)) {
                        $relativePath = str_replace(__DIR__ . '/', '', $file->getPathname());
                        $results[$key][] = [
                            'file' => $relativePath,
                            'line' => $index + 1,
                            'content' => trim($line)
                        ];
                    }
                }
            }
        }
    }
}

file_put_contents(__DIR__ . '/storage/logs/audit_results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Audit complete. Results saved to storage/logs/audit_results.json\n";
