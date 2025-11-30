# K8sResource Base Class

The `K8sResource` class is the foundation for all Kubernetes resources in PHP K8s.

## Overview

All Kubernetes resource classes (Pod, Deployment, Service, etc.) extend `K8sResource`, which provides:

- Common attribute management
- Cluster interaction methods
- State tracking
- CRUD operations foundation
- Trait composition support

## Class Definition

```php
abstract class K8sResource
{
    protected static string $kind;
    protected static string $defaultVersion;
    protected static bool $namespaceable = true;

    protected array $attributes = [];
    protected ?KubernetesCluster $cluster = null;
}
```

## Creating Resource Instances

### Via K8s Facade

```php
use RenokiCo\PhpK8s\K8s;

$pod = K8s::pod($cluster);
$deployment = K8s::deployment($cluster);
$service = K8s::service($cluster);
```

### Direct Instantiation

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

$pod = new K8sPod($cluster, [
    'metadata' => [
        'name' => 'my-pod',
        'namespace' => 'default',
    ],
    'spec' => [
        'containers' => [...]
    ]
]);
```

## Common Methods

### Attribute Management

```php
// Set attribute
$pod->setAttribute('spec.containers.0.image', 'nginx:latest');

// Get attribute
$image = $pod->getAttribute('spec.containers.0.image');

// Set multiple attributes
$pod->setAttributes([
    'metadata.name' => 'my-pod',
    'metadata.namespace' => 'default',
]);

// Get all attributes
$attributes = $pod->getAttributes();

// To array
$array = $pod->toArray();

// To JSON
$json = $pod->toJson();
```

### Metadata Methods

```php
// Name
$pod->setName('my-pod');
$name = $pod->getName();

// Namespace
$pod->setNamespace('production');
$namespace = $pod->getNamespace();

// Labels
$pod->setLabels(['app' => 'web', 'env' => 'prod']);
$pod->addLabel('version', 'v1.0');
$labels = $pod->getLabels();
$value = $pod->getLabel('app');

// Annotations
$pod->setAnnotations(['key' => 'value']);
$pod->addAnnotation('description', 'My app pod');
$annotations = $pod->getAnnotations();
```

### Resource Information

```php
// API version
$apiVersion = $pod->getApiVersion();

// Kind
$kind = $pod->getKind();

// Resource version (from cluster)
$resourceVersion = $pod->getResourceVersion();

// UID (from cluster)
$uid = $pod->getUid();

// Creation timestamp
$created = $pod->getCreationTimestamp();

// Deletion timestamp (if being deleted)
$deleted = $pod->getDeletionTimestamp();
```

## CRUD Operations

### Create

```php
$pod->create();

// Check if created
if ($pod->isSynced()) {
    echo "Pod created successfully";
}
```

### Read

```php
// Get by name
$pod = $cluster->getPodByName('my-pod', 'default');

// Refresh from cluster
$pod->refresh();
```

### Update

```php
// Modify and update
$pod->setReplicas(5);
$pod->update();

// Or use createOrUpdate for idempotency
$pod->createOrUpdate();
```

### Delete

```php
if ($pod->delete()) {
    echo "Pod deleted";
}

// With options
$pod->delete(
    query: ['pretty' => 1],
    gracePeriod: 30,
    propagationPolicy: 'Foreground'
);
```

## State Tracking

```php
// Check if resource has been synced with cluster
$pod->isSynced();  // true after create() or get()

// Check if resource exists in cluster
$pod->exists();

// Check sync status
if (!$pod->isSynced()) {
    $pod->create();
}
```

## Patching

### JSON Patch

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$patch = new JsonPatch();
$patch
    ->replace('/spec/replicas', 5)
    ->add('/metadata/labels/version', 'v2.0');

$deployment->jsonPatch($patch);
```

### JSON Merge Patch

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('metadata.labels.version', 'v2.0');

$deployment->jsonMergePatch($patch);
```

## Watching

```php
// Watch a specific resource
$pod->watch(function ($type, $pod) {
    echo "{$type}: {$pod->getName()}\n";
    return false; // Continue watching
});
```

## Custom Resource Definition

### Extending K8sResource

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'MyCRD';
    protected static $defaultVersion = 'mygroup.io/v1';
    protected static $namespaceable = true;
}
```

### Using Traits

```php
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    protected static $kind = 'MyCRD';
    protected static $defaultVersion = 'mygroup.io/v1';
}
```

## Available Traits

Resources can use these traits for additional functionality:

- **HasSpec** - Manage spec section
- **HasStatus** - Read status section
- **HasMetadata** - Labels, annotations, name, namespace
- **HasSelector** - Label/field selectors
- **HasReplicas** - Replica management
- **HasPodTemplate** - Pod template spec
- **HasStorage** - Storage configuration
- **HasAutoscaling** - Autoscaling configuration

## Macros

Add custom methods to resources:

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

K8sPod::macro('changeDnsPolicy', function ($policy) {
    return $this->setAttribute('spec.dnsPolicy', $policy);
});

// Use the macro
$pod->changeDnsPolicy('ClusterFirst');
```

## Protected Properties

### Resource Definition

```php
// The Kubernetes kind (e.g., 'Pod', 'Deployment')
protected static string $kind;

// Default API version (e.g., 'v1', 'apps/v1')
protected static string $defaultVersion;

// Whether resource is namespaced
protected static bool $namespaceable = true;
```

### Instance Properties

```php
// Resource attributes (metadata, spec, status, etc.)
protected array $attributes = [];

// Cluster connection
protected ?KubernetesCluster $cluster = null;
```

## Example: Complete Resource Lifecycle

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create resource
$configMap = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('default')
    ->setLabels(['app' => 'myapp'])
    ->setData([
        'DATABASE_HOST' => 'mysql',
        'DATABASE_PORT' => '3306',
    ]);

// Check state before creation
echo "Synced: " . ($configMap->isSynced() ? 'Yes' : 'No') . "\n";  // No
echo "Exists: " . ($configMap->exists() ? 'Yes' : 'No') . "\n";    // No

// Create in cluster
$configMap->create();

// Check state after creation
echo "Synced: " . ($configMap->isSynced() ? 'Yes' : 'No') . "\n";  // Yes
echo "Exists: " . ($configMap->exists() ? 'Yes' : 'No') . "\n";    // Yes

// Get resource information
echo "Name: {$configMap->getName()}\n";
echo "Namespace: {$configMap->getNamespace()}\n";
echo "UID: {$configMap->getUid()}\n";
echo "Created: {$configMap->getCreationTimestamp()}\n";
echo "Resource Version: {$configMap->getResourceVersion()}\n";

// Modify
$configMap->setData(['NEW_KEY' => 'new_value']);
$configMap->update();

// Convert to array
$array = $configMap->toArray();
print_r($array);

// Convert to JSON
$json = $configMap->toJson();
echo $json;

// Delete
$configMap->delete();
```

## See Also

- [KubernetesCluster](/api-reference/kubernetes-cluster) - Cluster connection class
- [K8s Facade](/api-reference/k8s-facade) - Resource factory methods
- [Resource Model](/architecture/resource-model) - Architecture overview
- [Trait Composition](/architecture/trait-composition) - Available traits

---

*API reference for the K8sResource base class in cuppett/php-k8s fork*
