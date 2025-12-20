# LimitRange

LimitRanges enforce minimum and maximum resource limits in a namespace.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$limitRange = K8s::limitRange($cluster)
    ->setName('resource-limits')
    ->setNamespace('default')
    ->setLimits([
        [
            'type' => 'Container',
            'default' => [
                'cpu' => '500m',
                'memory' => '512Mi',
            ],
            'defaultRequest' => [
                'cpu' => '100m',
                'memory' => '128Mi',
            ],
        ],
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
