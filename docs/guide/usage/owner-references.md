# Owner References

Owner references establish parent-child relationships between resources, enabling automatic garbage collection and dependency tracking in Kubernetes operators.

## What are Owner References?

Owner references link a resource (child) to its owner (parent). When the parent is deleted, Kubernetes automatically deletes all children (unless `blockOwnerDeletion` is set). This is fundamental to Kubernetes' garbage collection system.

## Key Concepts

- **Owner**: The parent resource that manages the lifecycle of child resources
- **Dependent**: Child resources with owner references
- **Controller Owner**: One owner can be marked as the controller (only one per resource)
- **Block Owner Deletion**: Prevents parent deletion until child is deleted

## Managing Owner References

### Get Owner References

```php
$pod = $cluster->getPodByName('my-pod', 'default');

$owners = $pod->getOwnerReferences();
// Returns array of owner references
```

### Set Owner References

```php
$pod->setOwnerReferences([
    [
        'apiVersion' => 'apps/v1',
        'kind' => 'ReplicaSet',
        'name' => 'my-replicaset',
        'uid' => '12345-67890-abcdef',
        'controller' => true,
    ],
]);
```

### Add an Owner Reference

The `addOwnerReference()` method is idempotent (matched by UID) and requires the owner to be synced with the cluster:

```php
// Owner must be created first (needs UID)
$deployment = $cluster->deployment()
    ->setName('my-app')
    ->setNamespace('production')
    // ... configure deployment ...
    ->create();

// Now create child with owner reference
$configMap = $cluster->configMap()
    ->setName('app-config')
    ->setNamespace('production')
    ->setData(['key' => 'value'])
    ->addOwnerReference($deployment)  // Deployment must have UID
    ->create();
```

### Add with Controller Flag

Mark one owner as the controller (responsible for managing this resource):

```php
$pod->addOwnerReference($replicaSet, controller: true);
```

### Add with Block Owner Deletion

Prevent the owner from being deleted until this child is deleted:

```php
$pvc->addOwnerReference($statefulSet, blockOwnerDeletion: true);
```

### Remove an Owner Reference

```php
$pod->removeOwnerReference($replicaSet);
```

### Check for an Owner Reference

```php
if ($pod->hasOwnerReference($deployment)) {
    echo "Pod is owned by deployment";
}
```

### Get Controller Owner

Get the owner reference marked as controller:

```php
$controller = $pod->getControllerOwner();

if ($controller) {
    echo "Controller: {$controller['kind']}/{$controller['name']}";
} else {
    echo "No controller owner";
}
```

## Operator Pattern Examples

### Parent-Child Resource Management

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create parent ConfigMap
$parent = $cluster->configMap()
    ->setName('parent-config')
    ->setNamespace('default')
    ->setData(['role' => 'parent'])
    ->create();

// Create child ConfigMap with owner reference
$child = $cluster->configMap()
    ->setName('child-config')
    ->setNamespace('default')
    ->setData(['role' => 'child'])
    ->addOwnerReference($parent, controller: true)
    ->create();

// When parent is deleted, child is automatically deleted by Kubernetes
$parent->delete();

// Wait a moment...
sleep(2);

try {
    $cluster->getConfigMapByName('child-config', 'default');
} catch (\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException $e) {
    echo "Child was automatically deleted\n";
}
```

### Multi-Level Ownership

```php
// Top-level resource
$app = $cluster->configMap()
    ->setName('my-app')
    ->create();

// Mid-level resource (owned by app)
$database = $cluster->configMap()
    ->setName('database')
    ->addOwnerReference($app, controller: true)
    ->create();

// Low-level resource (owned by database)
$credentials = $cluster->secret()
    ->setName('db-credentials')
    ->setData(['password' => base64_encode('secret')])
    ->addOwnerReference($database, controller: true)
    ->create();

// Deleting app cascades deletion through the hierarchy
$app->delete();
```

### Preventing Accidental Deletion

Use `blockOwnerDeletion` for critical dependencies:

```php
// Create PVC first
$pvc = $cluster->persistentVolumeClaim()
    ->setName('data-volume')
    ->setNamespace('production')
    // ... configure PVC ...
    ->create();

// StatefulSet owns PVC but cannot be deleted until PVC is deleted
$statefulSet = $cluster->statefulSet()
    ->setName('database')
    ->setNamespace('production')
    // ... configure StatefulSet ...
    ->create();

// Add owner reference with blocking
$pvc->addOwnerReference($statefulSet, blockOwnerDeletion: true)
    ->update();

// Attempting to delete StatefulSet will fail until PVC is manually deleted
```

### Reconciliation Loop with Owner Tracking

```php
function reconcile($cluster, $parentName, $namespace) {
    // Get parent resource
    $parent = $cluster->getConfigMapByName($parentName, $namespace);

    // Define desired child resources
    $desiredChildren = [
        'child-1' => ['data' => 'value1'],
        'child-2' => ['data' => 'value2'],
    ];

    foreach ($desiredChildren as $childName => $data) {
        try {
            // Get existing child
            $child = $cluster->getConfigMapByName($childName, $namespace);

            // Verify ownership
            if (!$child->hasOwnerReference($parent)) {
                echo "Adopting orphaned resource: $childName\n";
                $child->addOwnerReference($parent, controller: true)->update();
            }

            // Update if needed
            if ($child->getData() !== $data) {
                $child->setData($data)->update();
            }
        } catch (\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException $e) {
            // Create missing child
            echo "Creating child: $childName\n";
            $cluster->configMap()
                ->setName($childName)
                ->setNamespace($namespace)
                ->setData($data)
                ->addOwnerReference($parent, controller: true)
                ->create();
        }
    }

    // Clean up orphaned children
    $allChildren = $cluster->getAllConfigMaps($namespace);

    foreach ($allChildren as $child) {
        if ($child->hasOwnerReference($parent) &&
            !isset($desiredChildren[$child->getName()])) {
            echo "Deleting orphaned child: {$child->getName()}\n";
            $child->delete();
        }
    }
}
```

## Best Practices

### Always Set Owner on Creation

Add owner references when creating child resources:

```php
// ✅ GOOD - owner set at creation
$child = $cluster->configMap()
    ->setName('child')
    ->addOwnerReference($parent)
    ->create();

// ⚠️ LESS IDEAL - requires second API call
$child = $cluster->configMap()
    ->setName('child')
    ->create();
$child->addOwnerReference($parent)->update();
```

### Use Controller Flag Appropriately

Only one owner should be the controller:

```php
$pod->addOwnerReference($replicaSet, controller: true);   // Primary controller
$pod->addOwnerReference($deployment, controller: false);  // Secondary owner
```

### Validate UID Before Adding

The owner must exist in the cluster (have a UID):

```php
try {
    $child->addOwnerReference($parent);
} catch (\InvalidArgumentException $e) {
    echo "Parent must be created first (needs UID)\n";
}
```

### Cross-Namespace Ownership

Owner references **must** be in the same namespace as the owned resource:

```php
// ❌ WRONG - different namespaces
$parentInProd = $cluster->getConfigMapByName('parent', 'production');
$childInDev = $cluster->configMap()
    ->setName('child')
    ->setNamespace('dev')  // Different namespace!
    ->addOwnerReference($parentInProd)  // This won't work properly
    ->create();

// ✅ CORRECT - same namespace
$parent = $cluster->getConfigMapByName('parent', 'production');
$child = $cluster->configMap()
    ->setName('child')
    ->setNamespace('production')  // Same namespace
    ->addOwnerReference($parent)
    ->create();
```

### Idempotency

Adding the same owner reference multiple times is safe:

```php
$child->addOwnerReference($parent);
$child->addOwnerReference($parent);  // Safe - no duplicate created
```

## Common Use Cases

### Custom Resource Controller

```php
// Watch custom resources and manage children
$cluster->customResource()->watchAll(function ($type, $custom) use ($cluster) {
    if ($type === 'ADDED' || $type === 'MODIFIED') {
        reconcileChildren($cluster, $custom);
    }
    return true;  // Continue watching
});

function reconcileChildren($cluster, $parent) {
    // Create/update children with owner references
    foreach ($parent->getSpec('children', []) as $childSpec) {
        $child = $cluster->configMap()
            ->setName($childSpec['name'])
            ->setNamespace($parent->getNamespace())
            ->setData($childSpec['data'])
            ->addOwnerReference($parent, controller: true)
            ->createOrUpdate();
    }
}
```

### Dependency Management

```php
// Ensure database is created before app
$database = $cluster->statefulSet()
    ->setName('postgres')
    ->create();

$app = $cluster->deployment()
    ->setName('api-server')
    ->addOwnerReference($database)  // App depends on database
    ->create();

// Database cannot be deleted while app exists (if blockOwnerDeletion is set)
```

---

*Documentation for cuppett/php-k8s fork*
