# ResourceQuota

ResourceQuotas provide constraints that limit aggregate resource consumption per namespace.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$quota = K8s::resourceQuota($cluster)
    ->setName('compute-quota')
    ->setNamespace('development')
    ->setQuotas([
        'requests.cpu' => '10',
        'requests.memory' => '20Gi',
        'limits.cpu' => '20',
        'limits.memory' => '40Gi',
        'pods' => '20',
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
