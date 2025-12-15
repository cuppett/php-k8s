# HorizontalPodAutoscaler

Automatically scales the number of pods based on observed metrics.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$hpa = K8s::horizontalPodAutoscaler($cluster)
    ->setName('web-app-hpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'web-app')
    ->setMinReplicas(2)
    ->setMaxReplicas(10)
    ->setTargetCPUUtilizationPercentage(80)
    ->create();
```

## Get Status

```php
$hpa = $cluster->getHorizontalPodAutoscalerByName('web-app-hpa');

$hpa->refresh();

echo "Current: {$hpa->getCurrentReplicas()}\n";
echo "Desired: {$hpa->getDesiredReplicas()}\n";
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
