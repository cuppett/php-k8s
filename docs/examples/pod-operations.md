# Pod Operations

Advanced pod management examples.

## Create Pod with Multiple Containers

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$pod = K8s::pod($cluster)
    ->setName('multi-container-pod')
    ->setNamespace('default')
    ->setContainers([
        // Main application
        K8s::container()
            ->setName('app')
            ->setImage('myapp:v1.0')
            ->setPorts([K8s::containerPort()->setContainerPort(8080)]),

        // Sidecar for logging
        K8s::container()
            ->setName('log-collector')
            ->setImage('fluent/fluent-bit:latest')
    ])
    ->create();

echo "Created pod: {$pod->getName()}\n";
```

## Execute Commands

```php
$pod = $cluster->getPodByName('multi-container-pod');

// Execute in first container
$output = $pod->exec(['ls', '-la', '/app']);

// Execute in specific container
$output = $pod->exec(['whoami'], 'app');

foreach ($output as $line) {
    echo $line . "\n";
}
```

## Stream Logs

```php
$pod->watchLogs(function ($line) {
    echo "[APP] {$line}\n";
    return false;  // Continue watching
});
```

## Monitor Pod Status

```php
use RenokiCo\PhpK8s\Enums\PodPhase;

while ($pod->getPodPhase() !== PodPhase::RUNNING) {
    sleep(2);
    $pod->refresh();
    echo "Phase: {$pod->getPodPhase()->value}\n";
}

echo "Pod is running!\n";
echo "Pod IP: {$pod->getPodIp()}\n";
echo "Node: {$pod->getNodeName()}\n";
```

---

*Pod operations example for cuppett/php-k8s fork*
