# PodDisruptionBudget

PodDisruptionBudgets ensure availability during voluntary disruptions.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$pdb = K8s::podDisruptionBudget($cluster)
    ->setName('web-pdb')
    ->setNamespace('production')
    ->setSelectors(['app' => 'web'])
    ->setMinAvailable(2)  // or setMaxUnavailable(1)
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
