# Affinity

Configure pod and node affinity for scheduling preferences.

## Pod Affinity

Schedule pods close to other pods:

```php
use RenokiCo\PhpK8s\K8s;

$podAffinity = K8s::affinity()->podAffinity([
    'requiredDuringSchedulingIgnoredDuringExecution' => [
        [
            'labelSelector' => [
                'matchLabels' => ['app' => 'cache'],
            ],
            'topologyKey' => 'kubernetes.io/hostname',
        ],
    ],
]);

$pod->setPodAffinity($podAffinity);
```

## Pod Anti-Affinity

Schedule pods away from other pods:

```php
$podAntiAffinity = K8s::affinity()->podAntiAffinity([
    'preferredDuringSchedulingIgnoredDuringExecution' => [
        [
            'weight' => 100,
            'podAffinityTerm' => [
                'labelSelector' => [
                    'matchLabels' => ['app' => 'web'],
                ],
                'topologyKey' => 'kubernetes.io/hostname',
            ],
        ],
    ],
]);

$pod->setPodAntiAffinity($podAntiAffinity);
```

## Node Affinity

Schedule pods on specific nodes:

```php
$nodeAffinity = K8s::affinity()->nodeAffinity([
    'requiredDuringSchedulingIgnoredDuringExecution' => [
        'nodeSelectorTerms' => [
            [
                'matchExpressions' => [
                    [
                        'key' => 'disktype',
                        'operator' => 'In',
                        'values' => ['ssd'],
                    ],
                ],
            ],
        ],
    ],
]);

$pod->setNodeAffinity($nodeAffinity);
```

## See Also

- [Pod](/resources/workloads/pod) - Using affinity in pods

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
