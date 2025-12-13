# Scaling Resources

Scale your Kubernetes workloads programmatically with PHP K8s.

## Scale Deployments

### Simple Scaling

```php
$deployment = $cluster->getDeploymentByName('my-app', 'production');

// Scale to 5 replicas
$deployment->scale(5);

// Or use setReplicas and update
$deployment->setReplicas(10)->update();
```

### Get Current Scale

```php
$deployment->refresh();

echo "Desired: {$deployment->getReplicas()}\n";
echo "Ready: {$deployment->getReadyReplicas()}\n";
echo "Available: {$deployment->getAvailableReplicas()}\n";
echo "Updated: {$deployment->getUpdatedReplicas()}\n";
```

## Scale StatefulSets

```php
$statefulSet = $cluster->getStatefulSetByName('mysql', 'databases');

$statefulSet->scale(3);

// Wait for scaling
while ($statefulSet->getReadyReplicas() < 3) {
    sleep(5);
    $statefulSet->refresh();
}

echo "StatefulSet scaled to 3 replicas";
```

## Scale ReplicaSets

```php
$replicaSet = $cluster->getReplicaSetByName('app-rs');

$replicaSet->scale(8);
```

## Horizontal Pod Autoscaling

Create and manage HorizontalPodAutoscalers:

```php
use RenokiCo\PhpK8s\K8s;

$hpa = K8s::horizontalPodAutoscaler($cluster)
    ->setName('my-app-hpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'my-app')
    ->setMinReplicas(2)
    ->setMaxReplicas(10)
    ->setTargetCPUUtilizationPercentage(80)
    ->create();
```

### Get HPA Status

```php
$hpa = $cluster->getHorizontalPodAutoscalerByName('my-app-hpa');

echo "Min: {$hpa->getMinReplicas()}\n";
echo "Max: {$hpa->getMaxReplicas()}\n";
echo "Current: {$hpa->getCurrentReplicas()}\n";
echo "Desired: {$hpa->getDesiredReplicas()}\n";
```

## Vertical Pod Autoscaling

```php
$vpa = K8s::verticalPodAutoscaler($cluster)
    ->setName('my-app-vpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'my-app')
    ->setUpdateMode('Auto')  // or 'Off', 'Initial', 'Recreate'
    ->create();
```

## Advanced Scaling

### Scale with Timeout

```php
function scaleWithTimeout($deployment, int $replicas, int $timeout = 300): bool
{
    $deployment->scale($replicas);

    $start = time();

    while (time() - $start < $timeout) {
        $deployment->refresh();

        if ($deployment->getReadyReplicas() === $replicas) {
            return true;
        }

        sleep(5);
    }

    return false;
}

if (scaleWithTimeout($deployment, 5, 180)) {
    echo "Scaled successfully";
} else {
    echo "Timeout waiting for scale";
}
```

### Progressive Scaling

```php
$deployment = $cluster->getDeploymentByName('my-app');

// Scale progressively from 1 to 10
for ($i = 1; $i <= 10; $i++) {
    $deployment->scale($i);

    // Wait for pods to be ready
    do {
        sleep(5);
        $deployment->refresh();
    } while ($deployment->getReadyReplicas() < $i);

    echo "Scaled to {$i} replicas\n";
}
```

### Scale Based on Metrics

```php
function autoScale($deployment, array $metrics): void
{
    $currentLoad = $metrics['cpu_usage'];
    $current Replicas = $deployment->getReplicas();

    if ($currentLoad > 80 && $currentReplicas < 10) {
        $deployment->scale($currentReplicas + 2);
        echo "Scaling up to " . ($currentReplicas + 2);
    } elseif ($currentLoad < 20 && $currentReplicas > 2) {
        $deployment->scale($currentReplicas - 1);
        echo "Scaling down to " . ($currentReplicas - 1);
    }
}

$metrics = ['cpu_usage' => 85];
autoScale($deployment, $metrics);
```

## Best Practices

1. **Use HPA for automatic scaling** - Let Kubernetes handle it
2. **Set resource requests/limits** - Required for HPA to work
3. **Test scaling in staging** - Ensure your app scales well
4. **Monitor during scale** - Watch for issues
5. **Use PodDisruptionBudgets** - Ensure availability during scale-down
6. **Consider StatefulSet ordering** - StatefulSets scale sequentially

## Next Steps

- [Autoscaling Example](/examples/autoscaling) - Complete HPA/VPA example
- [Pod Disruption Budgets](/resources/policy/poddisruptionbudget) - Ensure availability

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
