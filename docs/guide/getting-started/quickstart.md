# Quick Start

This guide will walk you through the basics of using PHP K8s to interact with your Kubernetes cluster.

## Prerequisites

- PHP K8s installed (see [Installation](/guide/getting-started/installation))
- Access to a Kubernetes cluster
- Basic understanding of Kubernetes concepts

## Connect to Your Cluster

First, establish a connection to your Kubernetes cluster:

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\KubernetesCluster;

// Using a direct URL (e.g., kubectl proxy)
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Or using a kubeconfig file
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');
```

## Create a Namespace

Let's start by creating a namespace:

```php
use RenokiCo\PhpK8s\K8s;

$namespace = K8s::namespace($cluster)
    ->setName('my-app')
    ->setLabels(['env' => 'development'])
    ->create();

echo "Created namespace: " . $namespace->getName() . "\n";
```

## Deploy a Pod

Now deploy a simple nginx pod:

```php
$pod = K8s::pod($cluster)
    ->setName('nginx-pod')
    ->setNamespace('my-app')
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

echo "Created pod: " . $pod->getName() . "\n";
echo "Pod phase: " . $pod->getPodPhase()->value . "\n";
```

## Get Pod Status

Wait for the pod to be running and check its status:

```php
// Refresh pod state from cluster
$pod->refresh();

// Check if pod is running
if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
    echo "Pod is running!\n";
    echo "Pod IP: " . $pod->getPodIp() . "\n";
}
```

## Create a Service

Expose the pod with a service:

```php
$service = K8s::service($cluster)
    ->setName('nginx-service')
    ->setNamespace('my-app')
    ->setSelectors(['app' => 'nginx'])
    ->setPorts([
        K8s::servicePort()
            ->setProtocol('TCP')
            ->setPort(80)
            ->setTargetPort(80)
    ])
    ->setType('ClusterIP')
    ->create();

echo "Created service: " . $service->getName() . "\n";
echo "Service IP: " . $service->getClusterIp() . "\n";
```

## Create a Deployment

For production workloads, use deployments instead of pods:

```php
$deployment = K8s::deployment($cluster)
    ->setName('nginx-deployment')
    ->setNamespace('my-app')
    ->setSelectors(['app' => 'nginx'])
    ->setReplicas(3)
    ->setTemplate([
        K8s::pod()
            ->setLabels(['app' => 'nginx'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()->setContainerPort(80)
                    ])
            ])
    ])
    ->create();

echo "Created deployment with " . $deployment->getReplicas() . " replicas\n";
```

## Scale a Deployment

Scale the deployment to 5 replicas:

```php
$deployment->scale(5);

// Or use setReplicas and update
$deployment->setReplicas(5)->update();

echo "Scaled deployment to " . $deployment->getReplicas() . " replicas\n";
```

## List Resources

List all pods in a namespace:

```php
$pods = $cluster->getAllPods('my-app');

foreach ($pods as $pod) {
    echo "Pod: " . $pod->getName() . " - Phase: " . $pod->getPodPhase()->value . "\n";
}
```

List all namespaces:

```php
$namespaces = $cluster->getAllNamespaces();

foreach ($namespaces as $ns) {
    echo "Namespace: " . $ns->getName() . "\n";
}
```

## Get a Specific Resource

Retrieve a specific pod by name:

```php
$pod = $cluster->getPodByName('nginx-pod', 'my-app');

echo "Pod: " . $pod->getName() . "\n";
echo "Created: " . $pod->getCreationTimestamp() . "\n";
```

## Update a Resource

Update labels on a pod:

```php
$pod->setLabels([
    'app' => 'nginx',
    'version' => 'v1.0',
    'environment' => 'production'
])->update();

echo "Updated pod labels\n";
```

## Delete Resources

Delete the pod:

```php
$pod->delete();

echo "Deleted pod: " . $pod->getName() . "\n";
```

Delete the entire deployment:

```php
$deployment->delete();

echo "Deleted deployment: " . $deployment->getName() . "\n";
```

## Import from YAML

You can also create resources from YAML:

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: Pod
metadata:
  name: yaml-pod
  namespace: my-app
spec:
  containers:
  - name: nginx
    image: nginx:latest
    ports:
    - containerPort: 80
YAML;

$pod = K8s::fromYaml($cluster, $yamlContent);
$pod->create();

echo "Created pod from YAML: " . $pod->getName() . "\n";
```

## Watch Resources

Watch for pod changes in real-time:

```php
$pod->watchAll(function ($type, $pod) {
    echo "Event: {$type} - Pod: {$pod->getName()} - Phase: {$pod->getPodPhase()->value}\n";

    if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
        return true; // Stop watching
    }
}, ['namespace' => 'my-app']);
```

## Stream Logs

Stream logs from a container:

```php
$pod->logs(function ($line) {
    echo "Log: {$line}\n";
});
```

## Execute Commands

Execute a command in a container:

```php
$output = $pod->exec(['ls', '-la', '/usr/share/nginx/html']);

foreach ($output as $line) {
    echo $line . "\n";
}
```

## Complete Example

Here's a complete example that creates, scales, and monitors a deployment:

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// Connect to cluster
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create namespace
$namespace = K8s::namespace($cluster)
    ->setName('quickstart-demo')
    ->create();

// Create deployment
$deployment = K8s::deployment($cluster)
    ->setName('nginx')
    ->setNamespace('quickstart-demo')
    ->setSelectors(['app' => 'nginx'])
    ->setReplicas(2)
    ->setTemplate([
        K8s::pod()
            ->setLabels(['app' => 'nginx'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()->setContainerPort(80)
                    ])
            ])
    ])
    ->create();

echo "✓ Created deployment: {$deployment->getName()}\n";

// Scale up
$deployment->scale(5);
echo "✓ Scaled to 5 replicas\n";

// Wait for pods to be ready
sleep(5);

// List pods
$pods = $cluster->getAllPods('quickstart-demo');
echo "✓ Found " . count($pods) . " pods\n";

foreach ($pods as $pod) {
    echo "  - {$pod->getName()}: {$pod->getPodPhase()->value}\n";
}

// Cleanup
$deployment->delete();
$namespace->delete();
echo "✓ Cleaned up resources\n";
```

## Next Steps

Now that you understand the basics, explore:

- [Authentication](/guide/getting-started/authentication) - Configure secure cluster access
- [Configuration](/guide/getting-started/configuration) - Advanced configuration options
- [CRUD Operations](/guide/usage/crud-operations) - Deep dive into resource management
- [Examples](/examples/basic-crud) - More practical examples

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
