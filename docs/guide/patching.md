# Patching Resources

PHP K8s supports both JSON Patch (RFC 6902) and JSON Merge Patch (RFC 7396) for precise updates to Kubernetes resources without replacing the entire object.

## Why Use Patching?

Traditional `update()` replaces the entire resource, which can:
- Overwrite concurrent changes
- Require fetching the full resource first
- Be inefficient for small changes

Patching allows:
- ✅ Atomic, targeted updates
- ✅ Concurrent-safe modifications
- ✅ Efficient network usage
- ✅ Server-side conflict resolution

## JSON Patch (RFC 6902)

JSON Patch provides precise, surgical updates with validation capabilities.

### Supported Operations

- `add` - Add a value at a specific path
- `remove` - Remove a value at a specific path
- `replace` - Replace a value at a specific path
- `move` - Move a value from one path to another
- `copy` - Copy a value from one path to another
- `test` - Test that a value at a path matches the expected value

### Basic Usage

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$deployment = $cluster->getDeploymentByName('my-app', 'production');

// Create a JSON Patch
$patch = new JsonPatch();
$patch
    ->test('/metadata/name', 'my-app')           // Verify name
    ->replace('/spec/replicas', 5)               // Scale to 5
    ->add('/metadata/labels/version', 'v2.0')    // Add label
    ->remove('/metadata/labels/deprecated');     // Remove label

// Apply the patch
$deployment->jsonPatch($patch);
```

### Array Format

You can also use arrays directly:

```php
$patchArray = [
    ['op' => 'test', 'path' => '/spec/replicas', 'value' => 3],
    ['op' => 'replace', 'path' => '/spec/replicas', 'value' => 5],
    ['op' => 'add', 'path' => '/metadata/labels/updated', 'value' => 'true'],
];

$deployment->jsonPatch($patchArray);
```

### Advanced Examples

#### Conditional Updates with Test

```php
$patch = new JsonPatch();
$patch
    ->test('/spec/replicas', 3)        // Only proceed if currently 3
    ->replace('/spec/replicas', 5);     // Scale to 5

try {
    $deployment->jsonPatch($patch);
    echo "Scaled successfully";
} catch (\Exception $e) {
    echo "Precondition failed - replica count has changed";
}
```

#### Update Container Image

```php
$patch = new JsonPatch();
$patch->replace(
    '/spec/template/spec/containers/0/image',
    'myapp:v2.0.0'
);

$deployment->jsonPatch($patch);
```

#### Add Environment Variable

```php
$patch = new JsonPatch();
$patch->add(
    '/spec/template/spec/containers/0/env/-',  // -  adds to end of array
    ['name' => 'NEW_VAR', 'value' => 'new_value']
);

$deployment->jsonPatch($patch);
```

#### Move a Label

```php
$patch = new JsonPatch();
$patch->move(
    '/metadata/labels/old-label',
    '/metadata/labels/new-label'
);

$deployment->jsonPatch($patch);
```

## JSON Merge Patch (RFC 7396)

JSON Merge Patch provides a simpler, more intuitive way to update resources by merging changes.

### Basic Usage

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

$deployment = $cluster->getDeploymentByName('my-app', 'production');

// Create a JSON Merge Patch
$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('metadata.labels.version', 'v2.0')
    ->remove('metadata.labels.deprecated');  // Sets to null

// Apply the patch
$deployment->jsonMergePatch($patch);
```

### Array Format

```php
$patchArray = [
    'spec' => [
        'replicas' => 5
    ],
    'metadata' => [
        'labels' => [
            'version' => 'v2.0',
            'deprecated' => null  // null removes the label
        ]
    ]
];

$deployment->jsonMergePatch($patchArray);
```

### Advanced Examples

#### Update Multiple Fields

```php
$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('spec.template.spec.containers.0.image', 'myapp:v2.0.0')
    ->set('metadata.annotations.updated-at', date('c'));

$deployment->jsonMergePatch($patch);
```

#### Update Nested Objects

```php
$patch = new JsonMergePatch([
    'spec' => [
        'template' => [
            'spec' => [
                'containers' => [
                    [
                        'name' => 'app',
                        'resources' => [
                            'limits' => [
                                'memory' => '1Gi',
                                'cpu' => '500m'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$deployment->jsonMergePatch($patch);
```

## When to Use Which

### Use JSON Patch When:

- ✅ You need atomic operations with validation
- ✅ You want to verify preconditions with `test`
- ✅ You need precise array element manipulation
- ✅ You want to move or copy values
- ✅ Order of operations matters

### Use JSON Merge Patch When:

- ✅ You want simple, intuitive updates
- ✅ You're updating multiple related fields
- ✅ You don't need precondition testing
- ✅ You prefer declarative syntax
- ✅ You're merging configuration changes

### Use Server Side Apply When:

- ✅ Multiple controllers manage the same resource
- ✅ You want automatic conflict detection
- ✅ You need field ownership tracking
- ✅ You're implementing a controller or operator
- ✅ You want declarative, idempotent operations

See the [Server Side Apply Guide](/guide/server-side-apply) for more details.

## HTTP Content Types

The library automatically sets the correct Content-Type headers:

- JSON Patch: `application/json-patch+json`
- JSON Merge Patch: `application/merge-patch+json`

## Practical Examples

### Rolling Update with Version Check

```php
$deployment = $cluster->getDeploymentByName('api');

// Only update if current version is v1.0
$patch = new JsonPatch();
$patch
    ->test('/metadata/labels/version', 'v1.0')
    ->replace('/spec/template/spec/containers/0/image', 'api:v1.1')
    ->replace('/metadata/labels/version', 'v1.1');

try {
    $deployment->jsonPatch($patch);
    echo "Rolling update initiated";
} catch (\Exception $e) {
    echo "Version mismatch - update aborted";
}
```

### Add Sidecar Container

```php
$sidecar = [
    'name' => 'logging',
    'image' => 'fluent/fluent-bit:latest',
    'volumeMounts' => [
        ['name' => 'logs', 'mountPath' => '/var/log']
    ]
];

$patch = new JsonPatch();
$patch->add('/spec/template/spec/containers/-', $sidecar);

$deployment->jsonPatch($patch);
```

### Update Resource Limits

```php
$patch = new JsonMergePatch([
    'spec' => [
        'template' => [
            'spec' => [
                'containers' => [
                    [
                        'name' => 'app',
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
    ]
]);

$deployment->jsonMergePatch($patch);
```

### Batch Label Updates

```php
$pods = $cluster->getAllPods('production');

$patch = new JsonMergePatch([
    'metadata' => [
        'labels' => [
            'updated-at' => date('Y-m-d'),
            'managed-by' => 'php-k8s'
        ]
    ]
]);

foreach ($pods as $pod) {
    $pod->jsonMergePatch($patch);
}
```

## Error Handling

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $patch = new JsonPatch();
    $patch
        ->test('/spec/replicas', 3)
        ->replace('/spec/replicas', 5);

    $deployment->jsonPatch($patch);
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 422) {
        echo "Patch validation failed: {$e->getMessage()}";
    } else {
        echo "Patch failed: {$e->getMessage()}";
    }
}
```

## Supported Resources

Both patch methods work with all Kubernetes resources that extend `K8sResource`:

- Deployments, StatefulSets, DaemonSets
- Pods, Jobs, CronJobs
- Services, Ingresses
- ConfigMaps, Secrets
- PersistentVolumes, PersistentVolumeClaims
- And all other standard Kubernetes resources

## Best Practices

1. **Use JSON Patch for critical updates** - The `test` operation prevents race conditions
2. **Use JSON Merge Patch for bulk changes** - Simpler syntax for multiple related updates
3. **Handle errors gracefully** - Wrap in try-catch blocks
4. **Validate paths** - Ensure JSON paths are correct
5. **Understand array semantics** - `-` appends to arrays, indices update specific elements
6. **Refresh after patching** - Call `->refresh()` to get updated state
7. **Test in development** - Verify patches work before production use

## Limitations

- JSON Merge Patch cannot remove array elements individually
- JSON Patch requires exact path knowledge
- Some operations may fail due to server-side validation
- Concurrent patches may conflict

## Next Steps

- [Server Side Apply](/guide/server-side-apply) - Declarative updates with field ownership
- [CRUD Operations](/guide/crud-operations) - Traditional update method
- [Scaling](/guide/scaling) - Scale resources
- [Examples](/examples/patching) - More patching examples

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
