# Role

Roles grant permissions within a namespace.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$role = K8s::role($cluster)
    ->setName('pod-reader')
    ->setNamespace('default')
    ->setRules([
        [
            'apiGroups' => [''],
            'resources' => ['pods'],
            'verbs' => ['get', 'list', 'watch'],
        ],
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
