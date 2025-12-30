# VerticalPodAutoscaler

Automatically adjusts CPU and memory requests/limits.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$vpa = K8s::verticalPodAutoscaler($cluster)
    ->setName('web-app-vpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'web-app')
    ->setUpdateMode('Auto')  // or 'Off', 'Initial', 'Recreate'
    ->create();
```

::: tip
VPA requires the VPA components to be installed in your cluster.
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
