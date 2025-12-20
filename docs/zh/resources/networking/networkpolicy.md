# NetworkPolicy

NetworkPolicies control traffic flow between pods.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$networkPolicy = K8s::networkPolicy($cluster)
    ->setName('allow-web')
    ->setNamespace('default')
    ->setPodSelector(['app' => 'web'])
    ->setIngress([
        [
            'from' => [
                ['podSelector' => ['app' => 'frontend']]
            ],
            'ports' => [
                ['protocol' => 'TCP', 'port' => 80]
            ]
        ]
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
