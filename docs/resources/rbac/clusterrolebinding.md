# ClusterRoleBinding

ClusterRoleBindings grant cluster-wide permissions defined in a ClusterRole.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$clusterRoleBinding = K8s::clusterRoleBinding($cluster)
    ->setName('app-cluster-reader')
    ->setClusterRole('cluster-reader')
    ->setSubjects([
        [
            'kind' => 'ServiceAccount',
            'name' => 'app-sa',
            'namespace' => 'default',
        ],
    ])
    ->create();
```

::: info
ClusterRoleBinding is cluster-scoped (not namespaced).
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
