# Deployment Management

Complete example for managing Kubernetes deployments.

## Create a Deployment

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$deployment = K8s::deployment($cluster)
    ->setName('web-app')
    ->setNamespace('production')
    ->setLabels(['app' => 'web', 'tier' => 'frontend'])
    ->setSelectors(['app' => 'web'])
    ->setReplicas(3)
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'web'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()->setContainerPort(80)
                    ])
                    ->minCpu('100m')->maxCpu('200m')
                    ->minMemory('128Mi')->maxMemory('256Mi')
            ])
    )
    ->create();

echo "Created deployment: {$deployment->getName()}\n";
```

## Scale Deployment

```php
// Scale to 5 replicas
$deployment->scale(5);

// Wait for scale completion
while ($deployment->getReadyReplicas() < 5) {
    sleep(2);
    $deployment->refresh();
    echo "Ready: {$deployment->getReadyReplicas()}/5\n";
}

echo "Scaling complete!\n";
```

## Update Deployment Image

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$patch = new JsonPatch();
$patch->replace('/spec/template/spec/containers/0/image', 'nginx:1.21');

$deployment->jsonPatch($patch);

echo "Rolling update initiated\n";
```

## Monitor Rollout

```php
$deployment->watchByName('web-app', function ($type, $dep) {
    $ready = $dep->getReadyReplicas();
    $desired = $dep->getReplicas();
    $updated = $dep->getUpdatedReplicas();

    echo "Rollout: {$ready}/{$desired} ready, {$updated} updated\n";

    if ($ready === $desired && $updated === $desired) {
        echo "Rollout complete!\n";
        return true;  // Stop watching
    }

    return false;
}, ['namespace' => 'production']);
```

## Delete Deployment

```php
if ($deployment->delete()) {
    echo "Deployment deleted\n";
}
```

---

*Deployment management example for cuppett/php-k8s fork*
