# ClusterRole

ClusterRoles grant cluster-wide permissions.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$clusterRole = K8s::clusterRole($cluster)
    ->setName('cluster-reader')
    ->setRules([
        [
            'apiGroups' => ['*'],
            'resources' => ['*'],
            'verbs' => ['get', 'list', 'watch'],
        ],
    ])
    ->create();
```

::: info
ClusterRole is cluster-scoped (not namespaced).
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
