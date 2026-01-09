# DaemonSet

DaemonSets ensure all (or some) nodes run a copy of a pod.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$daemonSet = K8s::daemonSet($cluster)
    ->setName('node-exporter')
    ->setNamespace('monitoring')
    ->setSelectors(['app' => 'node-exporter'])
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'node-exporter'])
            ->setContainers([
                K8s::container()
                    ->setName('node-exporter')
                    ->setImage('prom/node-exporter:latest')
            ])
    )
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
