# Autoscaling

Examples for horizontal and vertical pod autoscaling.

## Horizontal Pod Autoscaler

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$hpa = K8s::horizontalPodAutoscaler($cluster)
    ->setName('web-app-hpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'web-app')
    ->setMinReplicas(2)
    ->setMaxReplicas(10)
    ->setTargetCPUUtilizationPercentage(80)
    ->create();

echo "HPA created: {$hpa->getName()}\n";
```

## Monitor HPA

```php
$hpa = $cluster->getHorizontalPodAutoscalerByName('web-app-hpa');

$hpa->refresh();

echo "Current: {$hpa->getCurrentReplicas()}\n";
echo "Desired: {$hpa->getDesiredReplicas()}\n";
echo "Min: {$hpa->getMinReplicas()}\n";
echo "Max: {$hpa->getMaxReplicas()}\n";
```

## Vertical Pod Autoscaler

```php
$vpa = K8s::verticalPodAutoscaler($cluster)
    ->setName('web-app-vpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'web-app')
    ->setUpdateMode('Auto')
    ->create();

echo "VPA created: {$vpa->getName()}\n";
```

---

*Autoscaling example for cuppett/php-k8s fork*
