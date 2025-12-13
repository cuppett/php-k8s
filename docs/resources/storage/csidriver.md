# CSIDriver

Brief description of the CSIDriver resource and its purpose in Kubernetes.

## API Version

- **Kind**: `CSIDriver`
- **Version**: `storage.k8s.io/v1`
- **Namespaced**: 

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$resource = K8s::csidriver($cluster)
    ->setName('my-csidriver')
    ->setLabels(['app' => 'myapp'])
    // Configure resource-specific fields
    ->create();
```

## Common Operations

### Create

```php
$resource->create();
```

### Get

```php
$resource = $cluster->getCSIDriverByName('my-csidriver');
```

### Update

```php
$resource->setAttribute('spec.field', 'value')->update();
```

### Delete

```php
$resource->delete();
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

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Complete working example
$resource = K8s::csidriver($cluster)
    ->setName('example-csidriver')
    ->create();

echo "Created: {$resource->getName()}\n";
```

## See Also

- [Base Resource](/resources/base-resource) - Common resource methods
- [K8s Facade](/development/api-reference/k8s-facade) - Factory methods

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*