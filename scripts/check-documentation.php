#!/usr/bin/env php
<?php

/**
 * Check if all resources have documentation
 *
 * Usage: php scripts/check-documentation.php
 */

$srcKinds = glob(__DIR__ . '/../src/Kinds/K8s*.php');
$docsPath = __DIR__ . '/../docs/resources';

$undocumented = [];
$documented = [];

foreach ($srcKinds as $kindFile) {
    $className = basename($kindFile, '.php');
    $resourceName = strtolower(preg_replace('/^K8s/', '', $className));

    // Search for documentation file
    $found = false;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($docsPath)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $filename = strtolower($file->getFilename());
            if (str_contains($filename, $resourceName)) {
                $found = true;
                $documented[] = [
                    'class' => $className,
                    'resource' => $resourceName,
                    'doc' => $file->getPathname()
                ];
                break;
            }
        }
    }

    if (!$found) {
        $undocumented[] = [
            'class' => $className,
            'resource' => $resourceName,
            'file' => $kindFile
        ];
    }
}

// Report results
echo "\n";
echo "Documentation Coverage Report\n";
echo "============================\n\n";

echo "✓ Documented Resources: " . count($documented) . "\n";
foreach ($documented as $item) {
    echo "  - {$item['class']}\n";
}

echo "\n";

if (!empty($undocumented)) {
    echo "⚠️  Undocumented Resources: " . count($undocumented) . "\n";
    foreach ($undocumented as $item) {
        echo "  - {$item['class']} ({$item['resource']})\n";
    }
    echo "\n";
    echo "To document these resources:\n";
    echo "  1. Create docs/resources/category/{resource-name}.md\n";
    echo "  2. Use template: docs/_templates/resource-template.md\n";
    echo "  3. Add to sidebar in docs/.vitepress/config.mjs\n";
    echo "\n";
    exit(1);
}

echo "✓ All resources are documented!\n\n";
exit(0);
