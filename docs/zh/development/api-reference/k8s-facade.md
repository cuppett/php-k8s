# K8s Facade

The `K8s` class provides convenient factory methods for creating Kubernetes resources.

## Overview

The `K8s` facade offers a clean, fluent interface for instantiating resources without directly calling constructors.

```php
use RenokiCo\PhpK8s\K8s;

// Clean and readable
$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setContainers([...]);

// Instead of
$pod = new K8sPod($cluster, [...]);
```

## Resource Factory Methods

### Workloads

```php
// Pod
$pod = K8s::pod($cluster);

// Deployment
$deployment = K8s::deployment($cluster);

// StatefulSet
$statefulSet = K8s::statefulSet($cluster);

// DaemonSet
$daemonSet = K8s::daemonSet($cluster);

// Job
$job = K8s::job($cluster);

// CronJob
$cronJob = K8s::cronJob($cluster);

// ReplicaSet
$replicaSet = K8s::replicaSet($cluster);
```

### Configuration & Storage

```php
// ConfigMap
$configMap = K8s::configMap($cluster);

// Secret
$secret = K8s::secret($cluster);

// PersistentVolume
$pv = K8s::persistentVolume($cluster);

// PersistentVolumeClaim
$pvc = K8s::persistentVolumeClaim($cluster);

// StorageClass
$storageClass = K8s::storageClass($cluster);
```

### Networking

```php
// Service
$service = K8s::service($cluster);

// Ingress
$ingress = K8s::ingress($cluster);

// NetworkPolicy
$networkPolicy = K8s::networkPolicy($cluster);

// EndpointSlice
$endpointSlice = K8s::endpointSlice($cluster);
```

### Autoscaling & Policy

```php
// HorizontalPodAutoscaler
$hpa = K8s::horizontalPodAutoscaler($cluster);

// VerticalPodAutoscaler
$vpa = K8s::verticalPodAutoscaler($cluster);

// ResourceQuota
$quota = K8s::resourceQuota($cluster);

// LimitRange
$limitRange = K8s::limitRange($cluster);

// PodDisruptionBudget
$pdb = K8s::podDisruptionBudget($cluster);

// PriorityClass
$priorityClass = K8s::priorityClass($cluster);
```

### RBAC

```php
// ServiceAccount
$sa = K8s::serviceAccount($cluster);

// Role
$role = K8s::role($cluster);

// ClusterRole
$clusterRole = K8s::clusterRole($cluster);

// RoleBinding
$roleBinding = K8s::roleBinding($cluster);

// ClusterRoleBinding
$clusterRoleBinding = K8s::clusterRoleBinding($cluster);
```

### Webhooks

```php
// ValidatingWebhookConfiguration
$vwc = K8s::validatingWebhookConfiguration($cluster);

// MutatingWebhookConfiguration
$mwc = K8s::mutatingWebhookConfiguration($cluster);
```

### Cluster Resources

```php
// Namespace
$namespace = K8s::namespace($cluster);

// Node
$node = K8s::node($cluster);

// Event
$event = K8s::event($cluster);
```

## Instance Helpers

### Container

```php
$container = K8s::container()
    ->setName('nginx')
    ->setImage('nginx:latest')
    ->setPorts([
        K8s::containerPort()->setContainerPort(80)
    ])
    ->setEnv(['KEY' => 'value']);
```

### Container Port

```php
$port = K8s::containerPort()
    ->setName('http')
    ->setContainerPort(8080)
    ->setProtocol('TCP');
```

### Volume

```php
// ConfigMap volume
$volume = K8s::volume()
    ->name('config-volume')
    ->configMap('app-config');

// Secret volume
$volume = K8s::volume()
    ->name('secret-volume')
    ->secret('app-secrets');

// EmptyDir volume
$volume = K8s::volume()
    ->name('cache-volume')
    ->emptyDir();

// PersistentVolumeClaim volume
$volume = K8s::volume()
    ->name('data-volume')
    ->persistentVolumeClaim('data-pvc');
```

### Probe

```php
// HTTP probe
$probe = K8s::probe()
    ->http('/health', 8080)
    ->setInitialDelaySeconds(10)
    ->setPeriodSeconds(30);

// TCP probe
$probe = K8s::probe()
    ->tcp(3306)
    ->setInitialDelaySeconds(5);

// Command probe
$probe = K8s::probe()
    ->command(['cat', '/tmp/healthy'])
    ->setInitialDelaySeconds(15);
```

### Affinity

```php
// Pod affinity
$affinity = K8s::affinity()->podAffinity([
    'requiredDuringSchedulingIgnoredDuringExecution' => [
        [
            'labelSelector' => [
                'matchLabels' => ['app' => 'cache'],
            ],
            'topologyKey' => 'kubernetes.io/hostname',
        ],
    ],
]);

// Node affinity
$affinity = K8s::affinity()->nodeAffinity([
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
```

### Service Port

```php
$port = K8s::servicePort()
    ->setName('http')
    ->setProtocol('TCP')
    ->setPort(80)
    ->setTargetPort(8080);
```

## YAML Import

```php
// From YAML string
$resource = K8s::fromYaml($cluster, $yamlString);

// From YAML file
$resource = K8s::fromYamlFile($cluster, '/path/to/manifest.yaml');

// From templated YAML
$resource = K8s::fromTemplatedYamlFile($cluster, '/path/to/template.yaml', [
    'app_name' => 'myapp',
    'replicas' => '3',
]);
```

## Custom Resource Registration

```php
// Register a CRD
K8s::registerCrd(IngressRoute::class, 'ingressRoute');

// Use it
$route = K8s::ingressRoute($cluster)
    ->setName('my-route')
    ->create();
```

## Macros

Add custom factory methods:

```php
K8s::macro('customResource', function ($cluster) {
    return new CustomResource($cluster);
});

// Use it
$resource = K8s::customResource($cluster);
```

## Complete Example

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create a complete deployment
$deployment = K8s::deployment($cluster)
    ->setName('web-app')
    ->setNamespace('production')
    ->setLabels(['app' => 'web', 'tier' => 'frontend'])
    ->setReplicas(3)
    ->setSelectors(['app' => 'web'])
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'web', 'tier' => 'frontend'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()
                            ->setName('http')
                            ->setContainerPort(80)
                    ])
                    ->setLivenessProbe(
                        K8s::probe()
                            ->http('/health', 80)
                            ->setInitialDelaySeconds(30)
                            ->setPeriodSeconds(10)
                    )
                    ->setReadinessProbe(
                        K8s::probe()
                            ->http('/ready', 80)
                            ->setInitialDelaySeconds(5)
                            ->setPeriodSeconds(5)
                    )
                    ->minCpu('100m')->maxCpu('500m')
                    ->minMemory('128Mi')->maxMemory('512Mi')
            ])
            ->addVolumes([
                K8s::volume()
                    ->name('config')
                    ->configMap('app-config')
            ])
    )
    ->create();

echo "Deployment created: {$deployment->getName()}\n";

// Create a service
$service = K8s::service($cluster)
    ->setName('web-service')
    ->setNamespace('production')
    ->setType('LoadBalancer')
    ->setSelectors(['app' => 'web'])
    ->setPorts([
        K8s::servicePort()
            ->setName('http')
            ->setProtocol('TCP')
            ->setPort(80)
            ->setTargetPort(80)
    ])
    ->create();

echo "Service created: {$service->getName()}\n";

// Create HPA
$hpa = K8s::horizontalPodAutoscaler($cluster)
    ->setName('web-hpa')
    ->setNamespace('production')
    ->setResource('Deployment', 'web-app')
    ->setMinReplicas(2)
    ->setMaxReplicas(10)
    ->setTargetCPUUtilizationPercentage(80)
    ->create();

echo "HPA created: {$hpa->getName()}\n";
```

## Method Signatures

All resource factory methods follow this signature:

```php
public static function resourceType(
    ?KubernetesCluster $cluster = null,
    array $attributes = []
): K8sResourceType
```

Example:

```php
// With cluster
$pod = K8s::pod($cluster);

// With cluster and attributes
$pod = K8s::pod($cluster, [
    'metadata' => ['name' => 'my-pod'],
    'spec' => ['containers' => [...]]
]);

// Without cluster (add later)
$pod = K8s::pod();
$pod->setCluster($cluster);
```

## Best Practices

1. **Use the facade** - Cleaner than direct instantiation
   ```php
   // Good
   $pod = K8s::pod($cluster);

   // Works, but verbose
   $pod = new K8sPod($cluster);
   ```

2. **Chain methods** - Fluent interface for readability
   ```php
   $pod = K8s::pod($cluster)
       ->setName('my-pod')
       ->setNamespace('default')
       ->setContainers([...])
       ->create();
   ```

3. **Reuse cluster** - Create once, pass to multiple resources
   ```php
   $cluster = new KubernetesCluster('...');

   $pod = K8s::pod($cluster);
   $service = K8s::service($cluster);
   $deployment = K8s::deployment($cluster);
   ```

4. **Use helper methods** - Container, volume, probe builders
   ```php
   K8s::container()->setName('app')->setImage('myapp:latest')
   K8s::volume()->name('data')->persistentVolumeClaim('data-pvc')
   K8s::probe()->http('/health', 80)
   ```

## See Also

- [KubernetesCluster](/development/api-reference/kubernetes-cluster) - Cluster connection
- [K8sResource](/development/api-reference/k8s-resource) - Base resource class
- [Quick Start](/guide/getting-started/quickstart) - Getting started guide
- [Examples](/examples/basic-crud) - Usage examples

---

*API reference for the K8s facade class in cuppett/php-k8s fork*
