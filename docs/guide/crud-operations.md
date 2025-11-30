# CRUD Operations

CRUD operations (Create, Read, Update, Delete) are available once you have [configured authentication](/getting-started/authentication) to your Kubernetes cluster.

## Retrieving All Resources

Get all resources of a specific type from a namespace:

```php
// Using the resource method
$namespaces = $cluster->namespace()->all();

// Using the convenience method (recommended)
$namespaces = $cluster->getAllNamespaces();

// For namespaced resources, specify the namespace
$stagingServices = $cluster->getAllServices('staging');
$defaultPods = $cluster->getAllPods('default');
```

::: tip Result Type
The result is an `RenokiCo\PhpK8s\ResourcesList` instance, which extends `\Illuminate\Support\Collection`, giving you access to all Laravel collection methods.
:::

### Collection Methods

Since results are collections, you can use powerful filtering and transformation methods:

```php
$pods = $cluster->getAllPods('production');

// Filter running pods
$runningPods = $pods->filter(fn($pod) =>
    $pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING
);

// Get pod names
$podNames = $pods->map(fn($pod) => $pod->getName());

// Count pods by phase
$podsByPhase = $pods->groupBy(fn($pod) => $pod->getPodPhase()->value);
```

## Retrieving Resources from All Namespaces

Get resources across all namespaces:

```php
// Using the resource method
$allPods = $cluster->pod()->allNamespaces();

// Using the convenience method (recommended)
$allPods = $cluster->getAllPodsFromAllNamespaces();
$allServices = $cluster->getAllServicesFromAllNamespaces();
```

## Retrieving a Specific Resource

Get a single resource by name:

```php
// Method 1: Using whereNamespace and whereName
$service = $cluster->service()
    ->whereNamespace('staging')
    ->whereName('nginx')
    ->get();

// Method 2: Using getByName (shorter)
$service = $cluster->service()
    ->whereNamespace('staging')
    ->getByName('nginx');

// Method 3: Using the convenience method (recommended)
$service = $cluster->getServiceByName('nginx', 'staging');

// Default namespace example
$pod = $cluster->getPodByName('my-pod'); // Uses 'default' namespace
```

::: info Default Namespace
By default, the namespace is `default` and can be omitted from function calls.
:::

## Creating Resources

Create a new resource in the cluster:

```php
// Create a namespace
$ns = $cluster->namespace()
    ->setName('staging')
    ->setLabels(['environment' => 'staging'])
    ->create();

// Check if resource is synced
$ns->isSynced(); // true

// Create a ConfigMap
$cm = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('production')
    ->setData([
        'APP_NAME' => 'MyApp',
        'APP_ENV' => 'production',
    ])
    ->create();

// Create a Pod
$pod = K8s::pod($cluster)
    ->setName('nginx-pod')
    ->setNamespace('default')
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
            ->setPorts([K8s::containerPort()->setContainerPort(80)])
    ])
    ->create();
```

### Checking Resource State

After creating a resource:

```php
$pod->isSynced(); // true - resource has been synced with cluster
$pod->exists(); // true - resource exists in cluster
$pod->getName(); // Returns the pod name
$pod->getNamespace(); // Returns the namespace
```

## Updating Resources

Update an existing resource using the REPLACE method:

```php
// Get the resource
$cm = $cluster->getConfigmapByName('env', 'default');

// Modify it
$cm->addData('API_KEY', '123')
    ->addData('API_SECRET', 'xyz')
    ->update();

// Update a deployment's replica count
$deployment = $cluster->getDeploymentByName('my-app');
$deployment->setReplicas(5)->update();

// Update pod labels
$pod = $cluster->getPodByName('my-pod');
$pod->setLabels([
    'app' => 'myapp',
    'version' => 'v2.0',
    'environment' => 'production'
])->update();
```

::: warning Update Method
The `update()` method uses Kubernetes REPLACE operation, which replaces the entire resource. For partial updates, use [JSON Patch](/guide/patching) instead.
:::

## Deleting Resources

Delete a resource from the cluster:

```php
// Simple delete
$cm = $cluster->getConfigmapByName('settings');

if ($cm->delete()) {
    echo 'ConfigMap deleted! 🎉';
}

// Delete a pod
$pod = $cluster->getPodByName('old-pod');
$pod->delete();
```

### Delete Options

The `delete()` method accepts optional parameters for fine-grained control:

```php
public function delete(
    array $query = ['pretty' => 1],
    ?int $gracePeriod = null,
    string $propagationPolicy = 'Foreground'
): bool
```

Example with options:

```php
// Delete with 30-second grace period
$pod->delete(
    query: ['pretty' => 1],
    gracePeriod: 30,
    propagationPolicy: 'Foreground'
);
```

**Propagation Policies:**
- `Foreground` - Waits for dependents to be deleted first
- `Background` - Deletes immediately, dependents deleted in background
- `Orphan` - Leaves dependents orphaned

## Creating or Updating Resources

Create a resource if it doesn't exist, or update it if it does:

```php
$cluster->configmap()
    ->setName('app-config')
    ->setNamespace('default')
    ->setData(['RAND' => mt_rand(0, 999)])
    ->createOrUpdate();
```

This is useful for idempotent operations:

```php
// Will create on first run, update on subsequent runs
K8s::secret($cluster)
    ->setName('api-credentials')
    ->setNamespace('production')
    ->setData('api-key', base64_encode('secret-value'))
    ->createOrUpdate();
```

## Batch Operations

Process multiple resources efficiently:

```php
// Create multiple namespaces
$namespaces = ['dev', 'staging', 'production'];

foreach ($namespaces as $ns) {
    K8s::namespace($cluster)
        ->setName($ns)
        ->setLabels(['managed-by' => 'php-k8s'])
        ->createOrUpdate();
}

// Delete old pods
$pods = $cluster->getAllPods('default');

$pods
    ->filter(fn($pod) => $pod->getAge() > 86400) // Older than 1 day
    ->each(fn($pod) => $pod->delete());
```

## Error Handling

Always wrap CRUD operations in try-catch blocks:

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('my-pod', 'production');
    $pod->setReplicas(3)->update();
} catch (KubernetesAPIException $e) {
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getCode();
    echo "Payload: " . json_encode($e->getPayload());
}
```

## Best Practices

1. **Use convenience methods** - `getAllPods()` is clearer than `pod()->all()`
2. **Always specify namespace in production** - Don't rely on defaults
3. **Use createOrUpdate for idempotency** - Safe for repeated operations
4. **Check existence before deleting** - Avoid unnecessary API calls
5. **Handle errors gracefully** - Always wrap in try-catch blocks
6. **Use collection methods** - Filter and transform results efficiently

## Examples

### Complete CRUD Workflow

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// CREATE
$cm = K8s::configMap($cluster)
    ->setName('app-settings')
    ->setNamespace('default')
    ->setData(['DEBUG' => 'true'])
    ->create();

echo "Created: {$cm->getName()}\n";

// READ
$cm = $cluster->getConfigmapByName('app-settings');
echo "Data: " . json_encode($cm->getData()) . "\n";

// UPDATE
$cm->setData(['DEBUG' => 'false', 'LOG_LEVEL' => 'info'])->update();
echo "Updated\n";

// DELETE
$cm->delete();
echo "Deleted\n";
```

## Next Steps

- [Import from YAML](/guide/yaml-import) - Load resources from YAML files
- [Patching](/guide/patching) - Perform partial updates with JSON Patch
- [Watching Resources](/guide/watching-resources) - Monitor changes in real-time

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
