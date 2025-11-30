# ReplicaSet

ReplicaSets maintain a stable set of replica pods running at any given time.

::: tip
You usually don't need to create ReplicaSets directly. Deployments manage ReplicaSets automatically.
:::

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$replicaSet = K8s::replicaSet($cluster)
    ->setName('frontend-rs')
    ->setNamespace('default')
    ->setReplicas(3)
    ->setSelectors(['app' => 'frontend'])
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'frontend'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
            ])
    )
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
