# Patching Examples

Examples of using JSON Patch, JSON Merge Patch, and Server Side Apply.

## JSON Patch Example

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Patches\JsonPatch;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$deployment = $cluster->getDeploymentByName('web-app');

$patch = new JsonPatch();
$patch
    ->test('/spec/replicas', 3)  // Verify current state
    ->replace('/spec/replicas', 5)  // Scale to 5
    ->add('/metadata/labels/version', 'v2.0')  // Add label
    ->remove('/metadata/labels/old');  // Remove label

$deployment->jsonPatch($patch);

echo "Deployment patched\n";
```

## JSON Merge Patch Example

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

$deployment = $cluster->getDeploymentByName('web-app');

$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('metadata.labels.version', 'v2.0')
    ->remove('metadata.labels.old');

$deployment->jsonMergePatch($patch);

echo "Deployment merged\n";
```

## Server Side Apply Example

### Basic Apply

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create or update a ConfigMap
$configmap = $cluster->configmap()
    ->setName('app-config')
    ->setNamespace('default')
    ->setLabels(['app' => 'myapp', 'env' => 'prod'])
    ->setData([
        'database.host' => 'db.example.com',
        'database.port' => '5432',
        'cache.enabled' => 'true'
    ]);

// Apply with field manager
$configmap->apply('my-controller');

echo "ConfigMap applied\n";
```

### Apply Deployment

```php
$deployment = $cluster->deployment()
    ->setName('web-app')
    ->setNamespace('default')
    ->setLabels(['app' => 'web'])
    ->setAttribute('spec', [
        'replicas' => 3,
        'selector' => [
            'matchLabels' => ['app' => 'web']
        ],
        'template' => [
            'metadata' => [
                'labels' => ['app' => 'web']
            ],
            'spec' => [
                'containers' => [
                    [
                        'name' => 'web',
                        'image' => 'nginx:1.21',
                        'ports' => [
                            ['containerPort' => 80]
                        ]
                    ]
                ]
            ]
        ]
    ]);

$deployment->apply('deployment-controller');

echo "Deployment applied\n";
```

### Handle Conflicts

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

$configmap = $cluster->configmap()
    ->setName('shared-config')
    ->setData(['key' => 'new-value']);

try {
    // Try to apply changes
    $configmap->apply('my-manager');
    echo "Applied successfully\n";
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        echo "Conflict detected - another manager owns this field\n";

        // Force take ownership
        $configmap->apply('my-manager', true);
        echo "Forced apply succeeded\n";
    } else {
        echo "Error: {$e->getMessage()}\n";
    }
}
```

### Multi-Manager Scenario

```php
// Controller 1 manages labels
$deployment = $cluster->deployment()
    ->setName('app')
    ->setLabels(['team' => 'platform', 'env' => 'prod']);

$deployment->apply('platform-controller');

// Controller 2 manages replicas (no conflict)
$deployment2 = $cluster->deployment()
    ->setName('app')
    ->setAttribute('spec.replicas', 5);

$deployment2->apply('autoscaler-controller');

echo "Both controllers applied their changes\n";
```

## Update Container Image

```php
$patch = new JsonPatch();
$patch->replace('/spec/template/spec/containers/0/image', 'myapp:v2.0.0');

$deployment->jsonPatch($patch);
```

---

*Patching examples for cuppett/php-k8s fork*
