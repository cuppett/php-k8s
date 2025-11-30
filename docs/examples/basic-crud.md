# Basic CRUD Operations

This example demonstrates the fundamental Create, Read, Update, and Delete (CRUD) operations with PHP K8s.

## Complete Example

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

// Connect to your cluster
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// CREATE - Create a new ConfigMap
echo "Creating ConfigMap...\n";

$configMap = K8s::configMap($cluster)
    ->setName('app-settings')
    ->setNamespace('default')
    ->setLabels([
        'app' => 'demo',
        'environment' => 'development'
    ])
    ->setData([
        'APP_NAME' => 'PHP K8s Demo',
        'APP_VERSION' => '1.0.0',
        'DEBUG' => 'true',
        'LOG_LEVEL' => 'debug'
    ]);

try {
    $configMap->create();
    echo "✓ Created ConfigMap: {$configMap->getName()}\n";
    echo "  Synced: " . ($configMap->isSynced() ? 'Yes' : 'No') . "\n";
} catch (KubernetesAPIException $e) {
    echo "✗ Error creating ConfigMap: {$e->getMessage()}\n";
    exit(1);
}

// READ - Retrieve the ConfigMap
echo "\nReading ConfigMap...\n";

try {
    $retrievedCm = $cluster->getConfigmapByName('app-settings', 'default');
    echo "✓ Retrieved ConfigMap: {$retrievedCm->getName()}\n";
    echo "  Data:\n";
    foreach ($retrievedCm->getData() as $key => $value) {
        echo "    {$key}: {$value}\n";
    }
    echo "  Labels:\n";
    foreach ($retrievedCm->getLabels() as $key => $value) {
        echo "    {$key}: {$value}\n";
    }
} catch (KubernetesAPIException $e) {
    echo "✗ Error reading ConfigMap: {$e->getMessage()}\n";
    exit(1);
}

// UPDATE - Modify the ConfigMap
echo "\nUpdating ConfigMap...\n";

try {
    $retrievedCm->setData([
        'APP_NAME' => 'PHP K8s Demo Updated',
        'APP_VERSION' => '1.1.0',
        'DEBUG' => 'false',
        'LOG_LEVEL' => 'info',
        'CACHE_ENABLED' => 'true' // New key
    ])->update();

    echo "✓ Updated ConfigMap\n";

    // Refresh to see changes
    $retrievedCm->refresh();
    echo "  Updated data:\n";
    foreach ($retrievedCm->getData() as $key => $value) {
        echo "    {$key}: {$value}\n";
    }
} catch (KubernetesAPIException $e) {
    echo "✗ Error updating ConfigMap: {$e->getMessage()}\n";
    exit(1);
}

// LIST - Get all ConfigMaps
echo "\nListing all ConfigMaps in default namespace...\n";

try {
    $configMaps = $cluster->getAllConfigmaps('default');
    echo "✓ Found {$configMaps->count()} ConfigMap(s)\n";

    foreach ($configMaps as $cm) {
        $age = time() - strtotime($cm->getCreationTimestamp());
        echo "  - {$cm->getName()} (age: {$age}s)\n";
    }
} catch (KubernetesAPIException $e) {
    echo "✗ Error listing ConfigMaps: {$e->getMessage()}\n";
}

// DELETE - Remove the ConfigMap
echo "\nDeleting ConfigMap...\n";

try {
    if ($retrievedCm->delete()) {
        echo "✓ Deleted ConfigMap: {$retrievedCm->getName()}\n";

        // Verify deletion
        sleep(2);
        try {
            $cluster->getConfigmapByName('app-settings', 'default');
            echo "✗ ConfigMap still exists!\n";
        } catch (KubernetesAPIException $e) {
            echo "✓ Confirmed: ConfigMap no longer exists\n";
        }
    }
} catch (KubernetesAPIException $e) {
    echo "✗ Error deleting ConfigMap: {$e->getMessage()}\n";
}

echo "\n✓ CRUD operations completed successfully!\n";
```

## Step-by-Step Breakdown

### 1. Connect to Cluster

```php
$cluster = new KubernetesCluster('http://127.0.0.1:8080');
```

For production, use proper authentication:

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');
```

### 2. Create a Resource

```php
$configMap = K8s::configMap($cluster)
    ->setName('app-settings')
    ->setNamespace('default')
    ->setData(['KEY' => 'value'])
    ->create();

if ($configMap->isSynced()) {
    echo "Resource created!";
}
```

### 3. Read a Resource

```php
// Get specific resource
$cm = $cluster->getConfigmapByName('app-settings', 'default');

// Access data
$data = $cm->getData();
$labels = $cm->getLabels();
```

### 4. Update a Resource

```php
// Modify attributes
$cm->setData(['UPDATED_KEY' => 'updated_value']);

// Apply changes
$cm->update();

// Refresh from cluster
$cm->refresh();
```

### 5. List Resources

```php
// Get all in namespace
$configMaps = $cluster->getAllConfigmaps('default');

// Filter using collections
$filtered = $configMaps->filter(function($cm) {
    return $cm->getLabel('app') === 'demo';
});
```

### 6. Delete a Resource

```php
if ($cm->delete()) {
    echo "Deleted successfully!";
}
```

## Error Handling

Always wrap operations in try-catch:

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('my-pod');
} catch (KubernetesAPIException $e) {
    echo "Error: {$e->getMessage()}";
    echo "Code: {$e->getCode()}";

    // Check specific errors
    if ($e->getCode() === 404) {
        echo "Pod not found";
    }
}
```

## Running the Example

1. Ensure you have a Kubernetes cluster running:
   ```bash
   minikube start
   kubectl proxy --port=8080 &
   ```

2. Run the example:
   ```bash
   php examples/basic-crud.php
   ```

3. Expected output:
   ```
   Creating ConfigMap...
   ✓ Created ConfigMap: app-settings
     Synced: Yes

   Reading ConfigMap...
   ✓ Retrieved ConfigMap: app-settings
     Data:
       APP_NAME: PHP K8s Demo
       APP_VERSION: 1.0.0
       ...

   Updating ConfigMap...
   ✓ Updated ConfigMap
     ...

   Deleting ConfigMap...
   ✓ Deleted ConfigMap: app-settings
   ✓ Confirmed: ConfigMap no longer exists

   ✓ CRUD operations completed successfully!
   ```

## Next Steps

- [Deployment Management](/examples/deployment-management) - Working with Deployments
- [Pod Operations](/examples/pod-operations) - Advanced Pod management
- [ConfigMap & Secrets](/examples/configmap-secrets) - Configuration management

---

*Example demonstrating basic CRUD operations in PHP K8s*
