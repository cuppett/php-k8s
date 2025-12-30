# ResourceName

Brief description of the ResourceName resource and its purpose in Kubernetes.

## API Version

- **Group**: `group.k8s.io` (or empty for core resources like Pod, Service)
- **Version**: `v1` (or apps/v1, networking.k8s.io/v1, etc.)
- **Kind**: `ResourceName`
- **Namespaced**: Yes/No

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$resource = K8s::resourceName($cluster)
    ->setName('my-resource')
    ->setNamespace('default')  # Remove this line for cluster-scoped resources
    ->setLabels(['app' => 'myapp'])
    // Add resource-specific configuration methods here
    ->create();
```

## Common Operations

### Create

```php
$resource->create();

// Verify creation
if ($resource->isSynced()) {
    echo "Resource created successfully";
}
```

### Get

```php
// Get by name
$resource = $cluster->getResourceNameByName('my-resource', 'namespace');

// Get all in namespace
$resources = $cluster->getAllResourceNames('namespace');

// Get from all namespaces (if namespaced)
$allResources = $cluster->getAllResourceNamesFromAllNamespaces();
```

### Update

```php
// Modify the resource
$resource->setAttribute('spec.field', 'new-value');

// Apply update
$resource->update();

// Or use createOrUpdate for idempotency
$resource->createOrUpdate();
```

### Delete

```php
if ($resource->delete()) {
    echo "Resource deleted successfully";
}

// With grace period
$resource->delete(
    query: ['pretty' => 1],
    gracePeriod: 30
);
```

## Resource-Specific Methods

Document methods that are unique to this resource type.

### setSpecificField()

Description of what this method does and when to use it.

```php
$resource->setSpecificField('value');
```

**Parameters:**
- `$value` (type) - Description

**Returns:** `self` for method chaining

### getSpecificField()

Get a specific field value.

```php
$value = $resource->getSpecificField();
```

**Returns:** (type) - Description

## Complete Example

Provide a complete, runnable example showing real-world usage.

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create the resource with all necessary configuration
$resource = K8s::resourceName($cluster)
    ->setName('example-resource')
    ->setNamespace('default')
    ->setLabels([
        'app' => 'myapp',
        'environment' => 'production'
    ])
    // Add specific configuration
    ->create();

echo "Created: {$resource->getName()}\n";

// Perform operations on the resource
// ...

// Clean up
$resource->delete();
```

## Related Resources

If this resource commonly works with other resources, document the patterns:

```php
// Example: Deployment + Service
$deployment = K8s::deployment($cluster)->setName('app')->create();
$service = K8s::service($cluster)->setName('app-svc')->setSelectors(['app' => 'app'])->create();
```

## Best Practices

1. **Practice 1** - Description and example
2. **Practice 2** - Description and example
3. **Practice 3** - Description and example

## Common Issues

### Issue 1

**Problem:** Description of the issue

**Solution:**
```php
// Solution code
```

## See Also

- [Related Resource 1](/resources/category/resource1) - When to use together
- [Related Resource 2](/resources/category/resource2) - Alternative approaches
- [Guide Name](/guide/guide-name) - Related guide
- [Example Name](/examples/example-name) - Practical example

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*

<!-- OR for new fork-only features: -->
<!-- *Documentation for cuppett/php-k8s fork* -->
