# Pod

Pods are the smallest deployable units in Kubernetes, consisting of one or more containers.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$pod = K8s::pod($cluster)
    ->setName('nginx-pod')
    ->setNamespace('default')
    ->setLabels(['app' => 'nginx'])
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
            ->setPorts([
                K8s::containerPort()->setContainerPort(80)
            ])
    ])
    ->create();
```

## Containers

### Set Containers

```php
$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ])
    ->setCommand(['mysqld'])
    ->setArgs(['--default-authentication-plugin=mysql_native_password'])
    ->setEnv([
        'MYSQL_ROOT_PASSWORD' => 'secret',
        'MYSQL_DATABASE' => 'myapp',
    ]);

$pod->setContainers([$container]);
```

### Get Containers

```php
// Get as Container instances
$containers = $pod->getContainers();

// Get as arrays
$containers = $pod->getContainers(false);

foreach ($containers as $container) {
    echo "Container: {$container->getName()}\n";
}
```

## Init Containers

```php
$initContainer = K8s::container()
    ->setName('init-db')
    ->setImage('busybox')
    ->setCommand(['sh', '-c', 'until nc -z mysql 3306; do sleep 1; done']);

$pod->setInitContainers([$initContainer]);
```

## Image Pull Secrets

```php
$pod->addPulledSecrets(['docker-registry-secret', 'another-secret']);
```

## Volumes

```php
$volume = K8s::volume()
    ->name('config-volume')
    ->configMap('app-config');

$pod->addVolumes([$volume]);
```

## Affinity

### Pod Affinity

```php
use RenokiCo\PhpK8s\K8s;

$podAffinity = K8s::affinity()->podAffinity([
    'requiredDuringSchedulingIgnoredDuringExecution' => [
        [
            'labelSelector' => [
                'matchLabels' => ['app' => 'database'],
            ],
            'topologyKey' => 'kubernetes.io/hostname',
        ],
    ],
]);

$pod->setPodAffinity($podAffinity);
```

### Node Affinity

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

## Pod Status

### Check Phase

```php
use RenokiCo\PhpK8s\Enums\PodPhase;

$pod->refresh();

$phase = $pod->getPodPhase();

if ($phase === PodPhase::RUNNING) {
    echo "Pod is running!";
} elseif ($phase === PodPhase::PENDING) {
    echo "Pod is pending...";
} elseif ($phase === PodPhase::FAILED) {
    echo "Pod failed!";
}
```

### Get Pod Details

```php
$pod->refresh();

echo "Pod IP: {$pod->getPodIp()}\n";
echo "Host IP: {$pod->getHostIp()}\n";
echo "Node: {$pod->getNodeName()}\n";
echo "QoS: {$pod->getQos()}\n";
echo "Phase: {$pod->getPodPhase()->value}\n";

// Check if running
if ($pod->isRunning()) {
    echo "Pod is active\n";
}
```

### Container Status

```php
// Get all container statuses
$containerStatuses = $pod->getContainerStatuses();

foreach ($containerStatuses as $status) {
    echo "Container: {$status['name']}\n";
    echo "Ready: " . ($status['ready'] ? 'Yes' : 'No') . "\n";
    echo "Restart Count: {$status['restartCount']}\n";
}

// Check if all containers are ready
if ($pod->containersAreReady()) {
    echo "All containers ready\n";
}

// Get init container statuses
$initStatuses = $pod->getInitContainerStatuses();
```

## Logging

### Get Logs

```php
// Get all logs
$logs = $pod->logs();
echo $logs;

// Get logs from specific container
$nginxLogs = $pod->containerLogs('nginx');

// Get logs with options
$logs = $pod->logs([
    'tailLines' => 100,
    'timestamps' => true,
    'sinceSeconds' => 3600,
]);
```

### Watch Logs

```php
$pod->watchLogs(function ($line) {
    echo "[LOG] {$line}\n";
    return false; // Continue watching
});

// Watch specific container
$pod->watchContainerLogs('nginx', function ($line) {
    echo "[NGINX] {$line}\n";
    return false;
});
```

## Execute Commands

```php
// Execute in first container
$output = $pod->exec(['ls', '-la', '/var/www']);

foreach ($output as $line) {
    echo $line . "\n";
}

// Execute in specific container
$output = $pod->exec(['whoami'], 'nginx');

// Run shell command
$output = $pod->exec(['/bin/sh', '-c', 'echo "Hello from pod"']);
```

## Advanced Features

### Restart Policy

```php
use RenokiCo\PhpK8s\Enums\RestartPolicy;

$pod->setRestartPolicy(RestartPolicy::ALWAYS);
// or RestartPolicy::ON_FAILURE
// or RestartPolicy::NEVER
```

### Service Account

```php
$pod->setServiceAccountName('my-service-account');
```

### Security Context

```php
$pod->setSecurityContext([
    'runAsUser' => 1000,
    'runAsGroup' => 3000,
    'fsGroup' => 2000,
]);
```

### DNS Policy

```php
$pod->setDnsPolicy('ClusterFirst');
// Options: ClusterFirstWithHostNet, ClusterFirst, Default, None
```

### Host Network

```php
$pod->setHostNetwork(true);
```

## Complete Example

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Enums\PodPhase;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create pod with multiple containers
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setNamespace('production')
    ->setLabels([
        'app' => 'myapp',
        'version' => 'v1.0',
        'environment' => 'production',
    ])
    ->setContainers([
        // Main application container
        K8s::container()
            ->setName('app')
            ->setImage('myapp:v1.0')
            ->setPorts([
                K8s::containerPort()->setContainerPort(8080)
            ])
            ->setEnv([
                'DATABASE_HOST' => 'mysql',
                'DATABASE_PORT' => '3306',
            ])
            ->minCpu('100m')->maxCpu('500m')
            ->minMemory('128Mi')->maxMemory('512Mi'),

        // Sidecar logging container
        K8s::container()
            ->setName('log-shipper')
            ->setImage('fluent/fluent-bit:latest')
    ])
    ->setInitContainers([
        K8s::container()
            ->setName('init')
            ->setImage('busybox')
            ->setCommand(['sh', '-c', 'echo Initializing...'])
    ])
    ->addPulledSecrets(['docker-registry'])
    ->setRestartPolicy(\RenokiCo\PhpK8s\Enums\RestartPolicy::ALWAYS)
    ->create();

// Wait for pod to be running
while ($pod->getPodPhase() !== PodPhase::RUNNING) {
    sleep(2);
    $pod->refresh();
    echo "Pod phase: {$pod->getPodPhase()->value}\n";
}

echo "Pod is running!\n";
echo "Pod IP: {$pod->getPodIp()}\n";

// Get logs
$logs = $pod->logs(['tailLines' => 50]);
echo "Recent logs:\n{$logs}\n";
```

## See Also

- [Container Instances](/api-reference/instances/container) - Container configuration
- [Volumes](/api-reference/instances/volume) - Volume types
- [Affinity](/api-reference/instances/affinity) - Pod and node affinity
- [Probes](/api-reference/instances/probe) - Liveness and readiness probes

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
