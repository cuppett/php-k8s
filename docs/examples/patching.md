# Patching Examples

Examples of using JSON Patch and JSON Merge Patch.

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

## Update Container Image

```php
$patch = new JsonPatch();
$patch->replace('/spec/template/spec/containers/0/image', 'myapp:v2.0.0');

$deployment->jsonPatch($patch);
```

---

*Patching examples for cuppett/php-k8s fork*
