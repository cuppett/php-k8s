#!/usr/bin/env php
<?php

/**
 * Helper to generate sidebar config from docs directory structure
 *
 * Usage: php scripts/update-sidebar.php
 *
 * Scans docs/ and suggests sidebar entries for new pages
 */

$docsPath = __DIR__ . '/../docs';
$categories = [
    'resources/cluster' => 'Cluster Resources',
    'resources/workloads' => 'Workloads',
    'resources/configuration' => 'Configuration',
    'resources/storage' => 'Storage',
    'resources/networking' => 'Networking',
    'resources/autoscaling' => 'Autoscaling',
    'resources/policy' => 'Policy',
    'resources/rbac' => 'RBAC',
    'resources/webhooks' => 'Webhooks',
];

echo "Sidebar Configuration Generator\n";
echo "================================\n\n";

foreach ($categories as $path => $title) {
    $fullPath = "{$docsPath}/{$path}";

    if (!is_dir($fullPath)) {
        continue;
    }

    $files = glob("{$fullPath}/*.md");

    if (empty($files)) {
        continue;
    }

    echo "## {$title}\n\n";
    echo "```javascript\n";
    echo "{\n";
    echo "  text: '{$title}',\n";
    echo "  collapsed: true,\n";
    echo "  items: [\n";

    foreach ($files as $file) {
        $filename = basename($file, '.md');
        $title = ucfirst($filename);
        $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $title);
        $link = "/{$path}/{$filename}";

        echo "    { text: '{$title}', link: '{$link}' },\n";
    }

    echo "  ]\n";
    echo "}\n";
    echo "```\n\n";
}

echo "\nCopy these snippets into docs/.vitepress/config.mjs sidebar configuration.\n";
