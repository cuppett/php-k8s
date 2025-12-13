# Server Side Apply

Server Side Apply (SSA) is Kubernetes' declarative approach to managing resources using field ownership tracking. Unlike traditional patching methods, SSA automatically tracks which controllers or users own which fields, enabling safe collaborative resource management.

## Why Use Server Side Apply?

Traditional `update()` and even patching can lead to conflicts when multiple controllers manage the same resource:
- Different controllers may overwrite each other's changes
- No built-in field ownership tracking
- Manual conflict resolution required
- Risk of losing important configuration

Server Side Apply provides:
- ✅ Automatic field ownership tracking
- ✅ Built-in conflict detection
- ✅ Safe multi-controller management
- ✅ Declarative configuration
- ✅ Idempotent operations
- ✅ Server-side validation and defaults

## Basic Usage

```php
$configmap = $cluster->configmap()
    ->setName('app-config')
    ->setLabels(['app' => 'my-app'])
    ->setData(['key1' => 'value1']);

// Apply with field manager identifier
$configmap->apply('my-controller');
```

The `fieldManager` parameter identifies who owns the fields being applied. Kubernetes tracks this in the resource's `metadata.managedFields`.

## Field Manager Concepts

### What is a Field Manager?

A field manager is an identifier (string) that represents the actor applying changes. Common examples:
- `'kubectl'` - Default for kubectl commands
- `'my-controller'` - Custom controller name
- `'php-k8s-app'` - Application identifier
- `'ops-team'` - Team or user identifier

### Field Ownership

When you apply a resource, Kubernetes records which fields your field manager owns. Multiple managers can coexist on the same resource, each managing different fields:

```php
// Manager 1 creates the resource with initial labels
$cm = $cluster->configmap()
    ->setName('shared-config')
    ->setLabels(['managed-by' => 'manager1'])
    ->setData(['key1' => 'value1']);

$cm->apply('manager1');

// Manager 2 adds different fields - no conflict
$cm2 = $cluster->configmap()
    ->setName('shared-config')
    ->setLabels(['version' => 'v2'])
    ->setData(['key2' => 'value2']);

$cm2->apply('manager2');  // Both managers' fields coexist
```

## Conflict Resolution

### Detecting Conflicts

If two managers try to modify the same field, Kubernetes returns a 409 Conflict error:

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    // Manager2 tries to modify field owned by manager1
    $cm = $cluster->configmap()
        ->setName('shared-config')
        ->setLabels(['managed-by' => 'manager2'])  // Conflict!
        ->setData(['key1' => 'different-value']);

    $cm->apply('manager2');
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        echo "Conflict detected: {$e->getMessage()}";
        $payload = $e->getPayload();
        echo "Reason: " . ($payload['reason'] ?? 'Unknown');
    }
}
```

### Force Apply

Use the `force` parameter to take ownership of conflicting fields:

```php
// Take ownership of fields from another manager
$cm = $cluster->configmap()
    ->setName('shared-config')
    ->setLabels(['managed-by' => 'manager2'])
    ->setData(['key1' => 'manager2-value']);

$cm->apply('manager2', true);  // force = true
```

**Warning:** Force apply should be used carefully as it transfers field ownership and may overwrite important configuration.

## Practical Examples

### Create a ConfigMap

```php
$configmap = $cluster->configmap()
    ->setName('api-config')
    ->setNamespace('production')
    ->setLabels(['app' => 'api', 'env' => 'prod'])
    ->setData([
        'database.host' => 'db.example.com',
        'database.port' => '5432',
        'cache.ttl' => '3600'
    ]);

$result = $configmap->apply('api-controller');

// Resource is created and field ownership is tracked
if ($result->isSynced()) {
    echo "ConfigMap created successfully";
}
```

### Update a Deployment

```php
$deployment = $cluster->deployment()
    ->setName('web-app')
    ->setNamespace('production')
    ->setLabels(['app' => 'web', 'version' => 'v2.0'])
    ->setAttribute('spec', [
        'replicas' => 3,
        'selector' => [
            'matchLabels' => ['app' => 'web']
        ],
        'template' => [
            'metadata' => [
                'labels' => ['app' => 'web', 'version' => 'v2.0']
            ],
            'spec' => [
                'containers' => [
                    [
                        'name' => 'web',
                        'image' => 'myapp:v2.0.0',
                        'ports' => [
                            ['containerPort' => 80]
                        ],
                        'resources' => [
                            'requests' => [
                                'cpu' => '100m',
                                'memory' => '128Mi'
                            ],
                            'limits' => [
                                'cpu' => '500m',
                                'memory' => '512Mi'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);

$result = $deployment->apply('deployment-controller');
```

### Create a Service

```php
$service = $cluster->service()
    ->setName('api-service')
    ->setNamespace('production')
    ->setLabels(['app' => 'api'])
    ->setAttribute('spec', [
        'selector' => ['app' => 'api'],
        'ports' => [
            [
                'name' => 'http',
                'protocol' => 'TCP',
                'port' => 80,
                'targetPort' => 8080
            ]
        ],
        'type' => 'ClusterIP'
    ]);

$result = $service->apply('api-controller');
```

### Idempotent Configuration Management

Server Side Apply is idempotent - applying the same configuration multiple times is safe:

```php
// Define desired state
$cm = $cluster->configmap()
    ->setName('app-config')
    ->setData(['replicas' => '3', 'log_level' => 'info']);

// First apply creates the resource
$cm->apply('config-manager');

// Subsequent applies with same data are no-ops
$cm->apply('config-manager');  // Safe to repeat
$cm->apply('config-manager');  // No conflicts
```

### Multi-Controller Scenario

```php
// Controller A manages application labels
$deployment = $cluster->deployment()
    ->setName('app')
    ->setLabels(['app' => 'myapp', 'team' => 'platform']);

$deployment->apply('platform-controller');

// Controller B manages deployment spec (different fields)
$deployment2 = $cluster->deployment()
    ->setName('app')
    ->setAttribute('spec.replicas', 5);

$deployment2->apply('autoscaler-controller');  // No conflict

// Both controllers can update their respective fields independently
```

## Managed Fields Tracking

After applying a resource, you can inspect field ownership:

```php
$configmap = $cluster->configmap()
    ->setName('test')
    ->setData(['key' => 'value']);

$result = $configmap->apply('my-manager');

// Inspect managed fields
$managedFields = $result->getAttribute('metadata.managedFields', []);

foreach ($managedFields as $field) {
    echo "Manager: {$field['manager']}\n";
    echo "Operation: {$field['operation']}\n";  // 'Apply' for SSA
    echo "Time: {$field['time']}\n";
    // $field['fieldsV1'] contains the actual fields managed
}
```

## Error Handling

### Conflict Errors (409)

```php
try {
    $cm = $cluster->configmap()
        ->setName('shared-config')
        ->setData(['key' => 'new-value']);

    $cm->apply('new-manager');
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        echo "Conflict: Another manager owns this field\n";

        // Option 1: Use force to take ownership
        $cm->apply('new-manager', true);

        // Option 2: Apply different fields instead
        // Option 3: Coordinate with the other manager
    }
}
```

### Validation Errors (400/422)

```php
try {
    $cm = $cluster->configmap()
        ->setName('Invalid-Name!')  // Invalid DNS name
        ->setData(['key' => 'value']);

    $cm->apply('my-manager');
} catch (KubernetesAPIException $e) {
    if (in_array($e->getCode(), [400, 422])) {
        echo "Validation failed: {$e->getMessage()}";
    }
}
```

### Namespace Not Found (404)

```php
try {
    $cm = $cluster->configmap()
        ->setName('test')
        ->setNamespace('non-existent')
        ->setData(['key' => 'value']);

    $cm->apply('my-manager');
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 404) {
        echo "Namespace does not exist";
    }
}
```

### Invalid Field Manager

```php
try {
    $cm->apply('');  // Empty field manager
} catch (KubernetesAPIException $e) {
    echo "Field manager cannot be empty";
}
```

## When to Use SSA vs JSON Patch vs Merge Patch

### Use Server Side Apply When:

- ✅ Multiple controllers manage the same resource
- ✅ You want automatic conflict detection
- ✅ You need field ownership tracking
- ✅ You're implementing a controller or operator
- ✅ You want declarative, idempotent operations
- ✅ You're managing complete resource configurations

### Use JSON Patch When:

- ✅ You need atomic operations with validation
- ✅ You want to verify preconditions with `test`
- ✅ You need precise array element manipulation
- ✅ You're making surgical, targeted updates

### Use JSON Merge Patch When:

- ✅ You want simple, one-off updates
- ✅ You're updating multiple related fields
- ✅ You don't need ownership tracking
- ✅ You prefer simple merge semantics

## Best Practices

1. **Use descriptive field managers** - Choose names that identify the actor (e.g., `'horizontal-pod-autoscaler'`, `'my-app-v1'`)

2. **Avoid force unless necessary** - Force apply transfers ownership and may cause conflicts with other managers

3. **Apply complete configurations** - SSA works best when you apply your complete desired state, not partial updates

4. **Handle conflicts gracefully** - Catch 409 errors and decide whether to force, retry, or coordinate

5. **Use consistent field managers** - The same controller should use the same field manager name

6. **Combine with watches** - Use watch operations to detect changes by other managers

7. **Test ownership scenarios** - Verify your application handles multi-manager situations correctly

## Limitations

- Force apply can silently overwrite other managers' fields
- Requires fieldManager parameter (cannot be empty)
- Server-side only (no client-side dry-run for field ownership)
- ManagedFields metadata can grow large with many managers
- Some older Kubernetes versions may not fully support SSA features

## HTTP Content Type

The library automatically sets the correct Content-Type header:
- Server Side Apply: `application/apply-patch+yaml`

## Supported Resources

Server Side Apply works with all Kubernetes resources that extend `K8sResource`:

- Deployments, StatefulSets, DaemonSets
- Pods, Jobs, CronJobs
- Services, Ingresses
- ConfigMaps, Secrets
- PersistentVolumes, PersistentVolumeClaims
- And all other standard Kubernetes resources

All resources implementing the `InteractsWithK8sCluster` contract have access to the `apply()` method.

## Next Steps

- [JSON Patch](/guide/patching#json-patch-rfc-6902) - Surgical updates with operations
- [JSON Merge Patch](/guide/patching#json-merge-patch-rfc-7396) - Simple merge-based patching
- [CRUD Operations](/guide/usage/crud-operations) - Traditional create/update/delete
- [Server Side Apply API Reference](/development/api-reference/patches/server-side-apply) - Detailed API documentation
- [Examples](/examples/patching) - More patching examples

---

*Documentation for cuppett/php-k8s fork*
