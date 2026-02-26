# Finalizers

Finalizers allow resources to perform cleanup operations before deletion. They're essential for building Kubernetes operators and controllers that manage dependent resources.

## What are Finalizers?

Finalizers are strings in `metadata.finalizers` that prevent a resource from being fully deleted until all finalizers are removed. When a resource with finalizers is deleted:

1. Kubernetes sets `metadata.deletionTimestamp`
2. The resource enters "Terminating" state
3. Controllers remove their finalizers after cleanup
4. Once all finalizers are removed, the resource is deleted

## Managing Finalizers

### Get Finalizers

```php
$configMap = $cluster->getConfigMapByName('my-config', 'default');

$finalizers = $configMap->getFinalizers();
// Returns: ['example.com/cleanup', 'example.com/backup']
```

### Set Finalizers

```php
$configMap->setFinalizers([
    'example.com/cleanup',
    'example.com/backup',
]);
```

### Add a Finalizer

The `addFinalizer()` method is idempotent - adding the same finalizer twice has no effect:

```php
$configMap->addFinalizer('example.com/cleanup');

// Safe to call multiple times
$configMap->addFinalizer('example.com/cleanup');
```

### Remove a Finalizer

```php
$configMap->removeFinalizer('example.com/cleanup');
```

### Check for a Finalizer

```php
if ($configMap->hasFinalizer('example.com/cleanup')) {
    echo "Cleanup finalizer is present";
}
```

## Operator Pattern Example

Here's a complete example of using finalizers in an operator:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');
$finalizerName = 'example.com/database-backup';

// When creating a resource
$configMap = $cluster->configMap()
    ->setName('database-config')
    ->setNamespace('production')
    ->setData(['connection' => 'postgresql://...'])
    ->addFinalizer($finalizerName)
    ->create();

// Later, in your reconciliation loop...
$configMap = $cluster->getConfigMapByName('database-config', 'production');

if ($configMap->getAttribute('metadata.deletionTimestamp')) {
    // Resource is being deleted
    echo "Performing cleanup before deletion...\n";

    // Do your cleanup (backup database, etc.)
    performDatabaseBackup($configMap);

    // Remove finalizer to allow deletion
    // IMPORTANT: Use jsonMergePatch, not update(), on resources being deleted
    $configMap->jsonMergePatch([
        'metadata' => [
            'finalizers' => array_values(
                array_filter(
                    $configMap->getFinalizers(),
                    fn($f) => $f !== $finalizerName
                )
            ),
        ],
    ]);

    echo "Cleanup complete, resource will be deleted\n";
} else {
    // Normal reconciliation
    echo "Resource is active\n";
}
```

## Best Practices

### Finalizer Naming

Use domain-prefixed names to avoid conflicts:

```php
// Good
$pod->addFinalizer('mycompany.com/cleanup');
$pod->addFinalizer('myoperator.io/backup');

// Avoid
$pod->addFinalizer('cleanup');  // Too generic
```

### Removing Finalizers During Deletion

When a resource is being deleted (has `deletionTimestamp`), you **cannot** use `update()`. Use `jsonMergePatch()` instead:

```php
// ❌ WRONG - will fail with 400 Bad Request
$resource->removeFinalizer('my-finalizer')->update();

// ✅ CORRECT - use patch operations
$resource->jsonMergePatch([
    'metadata' => [
        'finalizers' => [],  // Or array without your finalizer
    ],
]);
```

### Idempotent Cleanup

Make your cleanup operations idempotent - they should be safe to run multiple times:

```php
function performCleanup($resource) {
    $backupId = $resource->getLabel('backup-id');

    if ($backupId && backupExists($backupId)) {
        deleteBackup($backupId);
    }

    // Safe to call even if backup doesn't exist
}
```

### Timeout Protection

Add timeouts to prevent stuck resources:

```php
$deletionTime = strtotime($configMap->getAttribute('metadata.deletionTimestamp'));
$gracePeriod = 300; // 5 minutes

if (time() - $deletionTime > $gracePeriod) {
    // Force remove finalizer after grace period
    $configMap->jsonMergePatch([
        'metadata' => ['finalizers' => []],
    ]);
}
```

## Common Use Cases

### Resource Dependency Management

```php
// Parent resource manages child lifecycle
$parent = $cluster->configMap()
    ->setName('parent-config')
    ->addFinalizer('example.com/delete-children')
    ->create();

// On deletion, clean up children
if ($parent->getAttribute('metadata.deletionTimestamp')) {
    $children = $cluster->getAllConfigMaps()->filter(function ($cm) use ($parent) {
        return $cm->getLabel('parent') === $parent->getName();
    });

    foreach ($children as $child) {
        $child->delete();
    }

    $parent->removeFinalizer('example.com/delete-children');
    $parent->jsonMergePatch([
        'metadata' => ['finalizers' => $parent->getFinalizers()],
    ]);
}
```

### External Resource Cleanup

```php
// Clean up external resources (S3 buckets, databases, etc.)
$backup = $cluster->configMap()
    ->setName('backup-config')
    ->addFinalizer('example.com/s3-cleanup')
    ->create();

if ($backup->getAttribute('metadata.deletionTimestamp')) {
    $bucketName = $backup->getData('s3-bucket');

    // Delete S3 bucket
    $s3Client->deleteBucket(['Bucket' => $bucketName]);

    // Remove finalizer
    $backup->jsonMergePatch([
        'metadata' => ['finalizers' => []],
    ]);
}
```

---

*Documentation for cuppett/php-k8s fork*
