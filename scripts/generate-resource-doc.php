#!/usr/bin/env php
<?php

/**
 * Generate documentation stub for a new resource
 *
 * Usage: php scripts/generate-resource-doc.php K8sDeployment workloads
 */

if ($argc < 3) {
    echo "Usage: php scripts/generate-resource-doc.php <ClassName> <category>\n";
    echo "Example: php scripts/generate-resource-doc.php K8sDeployment workloads\n";
    echo "\nCategories: cluster, workloads, configuration, storage, networking, autoscaling, policy, rbac, webhooks\n";
    exit(1);
}

$className = $argv[1];
$category = $argv[2];

// Extract resource name
$resourceName = preg_replace('/^K8s/', '', $className);
$resourceLower = strtolower($resourceName);

// Load the class to get metadata
$classFile = __DIR__ . "/../src/Kinds/{$className}.php";

if (!file_exists($classFile)) {
    echo "Error: Class file not found: {$classFile}\n";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

$fullClassName = "RenokiCo\\PhpK8s\\Kinds\\{$className}";

if (!class_exists($fullClassName)) {
    echo "Error: Class {$fullClassName} not found\n";
    exit(1);
}

// Get class metadata using reflection
$reflection = new ReflectionClass($fullClassName);
$props = $reflection->getDefaultProperties();

$kind = $props['kind'] ?? $resourceName;
$version = $props['defaultVersion'] ?? 'v1';
$namespaced = $props['namespaceable'] ?? true;

// Generate documentation
$docPath = __DIR__ . "/../docs/resources/{$category}/{$resourceLower}.md";

// Create directory if it doesn't exist
$docDir = dirname($docPath);
if (!is_dir($docDir)) {
    mkdir($docDir, 0755, true);
}

$template = <<<MARKDOWN
# {$resourceName}

Brief description of the {$resourceName} resource and its purpose in Kubernetes.

## API Version

- **Kind**: `{$kind}`
- **Version**: `{$version}`
- **Namespaced**: {$namespacedText}

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

\$resource = K8s::{$resourceLower}(\$cluster)
    ->setName('my-{$resourceLower}'){$namespaceMethod}
    ->setLabels(['app' => 'myapp'])
    // Configure resource-specific fields
    ->create();
```

## Common Operations

### Create

```php
\$resource->create();
```

### Get

```php
\$resource = \$cluster->get{$resourceName}ByName('my-{$resourceLower}'{$namespaceArg});
```

### Update

```php
\$resource->setAttribute('spec.field', 'value')->update();
```

### Delete

```php
\$resource->delete();
```

## Resource-Specific Methods

Document methods unique to this resource type.

### Method Name

```php
// Example method usage
```

## Complete Example

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

\$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Complete working example
\$resource = K8s::{$resourceLower}(\$cluster)
    ->setName('example-{$resourceLower}'){$namespaceMethod}
    ->create();

echo "Created: {\$resource->getName()}\\n";
```

## See Also

- [Base Resource](/resources/base-resource) - Common resource methods
- [K8s Facade](/api-reference/k8s-facade) - Factory methods

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
MARKDOWN;

// Replace placeholders
$namespacedText = $namespaced ? 'Yes' : 'No (cluster-scoped)';
$namespaceMethod = $namespaced ? "\n    ->setNamespace('default')" : '';
$namespaceArg = $namespaced ? ", 'default'" : '';

$template = str_replace('{$namespacedText}', $namespacedText, $template);
$template = str_replace('{$namespaceMethod}', $namespaceMethod, $template);
$template = str_replace('{$namespaceArg}', $namespaceArg, $template);

file_put_contents($docPath, $template);

echo "✓ Generated documentation:\n";
echo "  File: {$docPath}\n";
echo "  Resource: {$resourceName}\n";
echo "  Category: {$category}\n";
echo "\n";
echo "Next steps:\n";
echo "  1. Edit {$docPath} and fill in details\n";
echo "  2. Add to sidebar in docs/.vitepress/config.mjs:\n";
echo "     { text: '{$resourceName}', link: '/resources/{$category}/{$resourceLower}' }\n";
echo "  3. Build docs: npm run docs:build\n";
echo "\n";
