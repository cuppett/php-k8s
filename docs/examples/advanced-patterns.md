# Advanced Patterns

Advanced usage patterns for PHP K8s.

## Wait for Resource Ready

```php
function waitForDeploymentReady($deployment, int $timeout = 300): bool
{
    $start = time();

    while (time() - $start < $timeout) {
        $deployment->refresh();

        $ready = $deployment->getReadyReplicas();
        $desired = $deployment->getReplicas();

        if ($ready === $desired) {
            return true;
        }

        sleep(5);
    }

    return false;
}

if (waitForDeploymentReady($deployment)) {
    echo "Deployment is ready!";
}
```

## Batch Resource Creation

```php
$resources = [
    K8s::namespace($cluster)->setName('prod'),
    K8s::namespace($cluster)->setName('staging'),
    K8s::namespace($cluster)->setName('dev'),
];

foreach ($resources as $resource) {
    $resource->createOrUpdate();
}
```

## Resource Cleanup

```php
// Delete all pods with specific label
$pods = $cluster->getAllPods('default');

$pods
    ->filter(fn($pod) => $pod->getLabel('temporary') === 'true')
    ->each(fn($pod) => $pod->delete());
```

## Multi-Cluster Management

```php
$clusters = [
    'prod' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod'),
    'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging'),
];

foreach ($clusters as $env => $cluster) {
    $pods = $cluster->getAllPods();
    echo "{$env}: {$pods->count()} pods\n";
}
```

---

*Advanced patterns examples for cuppett/php-k8s fork*
