# RoleBinding

RoleBindings grant permissions defined in a Role to users or ServiceAccounts.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$roleBinding = K8s::roleBinding($cluster)
    ->setName('app-pod-reader')
    ->setNamespace('default')
    ->setRole('pod-reader')
    ->setSubjects([
        [
            'kind' => 'ServiceAccount',
            'name' => 'app-sa',
            'namespace' => 'default',
        ],
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
