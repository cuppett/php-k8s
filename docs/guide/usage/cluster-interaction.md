# Cluster Interaction

## Overview

Resources in PHP K8s interact directly with the Kubernetes API, allowing you to create, read, update, and delete resources using PHP. The core functionality is built around the `K8sResource` class, which provides the basic logic for connecting to your Kubernetes cluster.

## Key Concepts

### Resource Interaction

- Each resource extends the base `K8sResource` class
- Supports CRUD (Create, Read, Update, Delete) operations
- Can be used with or without YAML files
- Allows interaction with existing Kubernetes resources
- Particularly useful for creating custom resources (CRDs)

### Cluster Operations

You can perform operations from the `KubernetesCluster` instance, including:

- CRUD operations
- Importing existing YAML files
- Watching resources
- Performing callbacks on resources

## Creating a Cluster Connection

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Direct URL connection
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// From kubeconfig file
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');

// In-cluster configuration (when running inside a pod)
$cluster = KubernetesCluster::inClusterConfiguration();
```

## Resource Factory Methods

PHP K8s provides convenient factory methods for creating resources:

```php
use RenokiCo\PhpK8s\K8s;

// Create a pod resource
$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setNamespace('default');

// Create a deployment
$deployment = K8s::deployment($cluster)
    ->setName('my-app')
    ->setNamespace('production');

// Create a service
$service = K8s::service($cluster)
    ->setName('api-service')
    ->setNamespace('default');
```

## Working with Resources

### Building Resources

Resources are built using a fluent interface:

```php
$configMap = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('default')
    ->setLabels(['app' => 'myapp', 'environment' => 'production'])
    ->setData([
        'DATABASE_HOST' => 'mysql.example.com',
        'DATABASE_PORT' => '3306',
        'CACHE_DRIVER' => 'redis',
    ]);
```

### Retrieving Resources

```php
// Get a specific resource
$pod = $cluster->getPodByName('my-pod', 'default');

// Get all resources in a namespace
$pods = $cluster->getAllPods('default');

// Get all resources across all namespaces
$allPods = $cluster->getAllPodsFromAllNamespaces();
```

### Modifying Resources

```php
// Get a resource
$deployment = $cluster->getDeploymentByName('my-app', 'production');

// Modify it
$deployment->setReplicas(5);

// Update in cluster
$deployment->update();
```

## Resource State

PHP K8s tracks resource synchronization state:

```php
$pod = K8s::pod($cluster)->setName('test-pod');

// Check if resource has been synced with cluster
$pod->isSynced(); // false

// Create the resource
$pod->create();

// Now it's synced
$pod->isSynced(); // true

// Check if resource exists in cluster
$pod->exists(); // true
```

## Next Steps

- [CRUD Operations](/guide/usage/crud-operations) - Deep dive into create, read, update, delete
- [Import from YAML](/guide/usage/yaml-import) - Load resources from YAML files
- [Watching Resources](/guide/usage/watching-resources) - Monitor resource changes in real-time
- [Exec & Logs](/guide/usage/exec-logs) - Execute commands and stream logs

## Laravel Integration

If you're using Laravel, the `laravel-php-k8s` package provides additional conveniences:

```php
use RenokiCo\LaravelK8s\LaravelK8sFacade as K8s;

// Use default configured connection
$pods = K8s::getAllPods();

// Use specific connection
$pods = K8s::connection('production')->getAllPods();
```

## Best Practices

1. **Reuse cluster connections** - Create the cluster instance once and reuse it
2. **Use specific methods** - Prefer `getAllPods()` over `pod()->all()` for clarity
3. **Check resource state** - Always check `isSynced()` and `exists()` when needed
4. **Handle errors** - Wrap API calls in try-catch blocks for proper error handling
5. **Use namespaces** - Always specify namespaces explicitly for production code

## Error Handling

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('nonexistent-pod');
} catch (KubernetesAPIException $e) {
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getCode();
}
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
