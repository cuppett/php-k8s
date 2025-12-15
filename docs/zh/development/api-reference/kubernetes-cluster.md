# KubernetesCluster Class

The `KubernetesCluster` class is the main entry point for interacting with your Kubernetes cluster.

## Creating a Cluster Connection

### Direct URL

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
```

### From Kubeconfig

```php
// Default location (~/.kube/config)
$cluster = KubernetesCluster::fromKubeConfigYamlFile();

// Custom path
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');

// Specific context
$cluster = KubernetesCluster::fromKubeConfigYamlFile(null, 'production-context');
```

### In-Cluster Configuration

```php
// When running inside a Kubernetes pod
$cluster = KubernetesCluster::inClusterConfiguration();
```

## Authentication Methods

### Bearer Token

```php
$cluster->withToken('your-service-account-token');
```

### Client Certificates

```php
$cluster->withCertificate(
    '/path/to/client.crt',
    '/path/to/client.key',
    '/path/to/ca.crt'
);
```

### Basic Auth (Deprecated)

```php
$cluster->withBasicAuth('username', 'password');
```

## SSL Configuration

```php
// Disable SSL verification (development only!)
$cluster->withoutSslChecks();

// Custom CA certificate
$cluster->withCaCertificate('/path/to/ca.crt');
```

## HTTP Client Options

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443', [
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => true,
    'http_errors' => true,
]);
```

## Resource Factory Methods

All Kubernetes resources can be created via the cluster:

```php
// Pods
$pod = $cluster->pod();
$pods = $cluster->getAllPods('namespace');
$pod = $cluster->getPodByName('pod-name', 'namespace');

// Deployments
$deployment = $cluster->deployment();
$deployments = $cluster->getAllDeployments('namespace');
$deployment = $cluster->getDeploymentByName('name', 'namespace');

// Services
$service = $cluster->service();
$services = $cluster->getAllServices('namespace');
$service = $cluster->getServiceByName('name', 'namespace');

// ConfigMaps
$configMap = $cluster->configMap();
$configMaps = $cluster->getAllConfigmaps('namespace');
$configMap = $cluster->getConfigmapByName('name', 'namespace');

// And so on for all resource types...
```

## YAML Import

```php
// From YAML string
$resource = $cluster->fromYaml($yamlString);

// From YAML file
$resource = $cluster->fromYamlFile('/path/to/manifest.yaml');

// Templated YAML
$resource = $cluster->fromTemplatedYamlFile('/path/to/template.yaml', [
    'app_name' => 'myapp',
    'replicas' => '3',
]);
```

## Cluster Information

```php
// Get Kubernetes version
$version = $cluster->getVersion();

// Check API server connectivity
if ($cluster->ping()) {
    echo "Cluster is reachable";
}
```

## Configuration Methods

### Set Default Namespace

```php
$cluster->setDefaultNamespace('production');

// Now all operations default to 'production' namespace
$pods = $cluster->getAllPods(); // Gets pods from 'production'
```

### Set Timeout

```php
$cluster->setTimeout(60);  // 60 seconds
$cluster->setConnectionTimeout(10);  // 10 seconds
```

### Custom Headers

```php
$cluster->setHeaders([
    'X-Custom-Header' => 'value',
    'User-Agent' => 'MyApp/1.0',
]);
```

## Available Resource Methods

For each resource type, the cluster provides:

```php
// Factory method
$cluster->resourceType()

// Get all in namespace
$cluster->getAllResourceTypes('namespace')

// Get all from all namespaces
$cluster->getAllResourceTypesFromAllNamespaces()

// Get by name
$cluster->getResourceTypeByName('name', 'namespace')
```

### Supported Resources

- **Workloads**: pod, deployment, statefulSet, daemonSet, job, cronJob, replicaSet
- **Configuration**: configMap, secret
- **Storage**: persistentVolume, persistentVolumeClaim, storageClass
- **Networking**: service, ingress, networkPolicy, endpointSlice
- **Autoscaling**: horizontalPodAutoscaler, verticalPodAutoscaler
- **Policy**: resourceQuota, limitRange, podDisruptionBudget, priorityClass
- **RBAC**: serviceAccount, role, clusterRole, roleBinding, clusterRoleBinding
- **Webhooks**: validatingWebhookConfiguration, mutatingWebhookConfiguration
- **Cluster**: namespace, node, event

## Method Reference

### Authentication

- `withToken(string $token): self`
- `withCertificate(string $cert, string $key, ?string $ca = null): self`
- `withBasicAuth(string $username, string $password): self`
- `withCaCertificate(string $ca): self`
- `withoutSslChecks(): self`

### Configuration

- `setTimeout(int $seconds): self`
- `setConnectionTimeout(int $seconds): self`
- `setDefaultNamespace(string $namespace): self`
- `setHeaders(array $headers): self`

### YAML Operations

- `fromYaml(string $yaml): K8sResource|array`
- `fromYamlFile(string $path): K8sResource|array`
- `fromTemplatedYamlFile(string $path, array $replacements): K8sResource|array`

### Cluster Information

- `getVersion(): array`
- `ping(): bool`

## Complete Example

```php
<?php

use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\K8s;

// Create cluster with authentication
$cluster = KubernetesCluster::fromKubeConfigYamlFile(
    '/home/user/.kube/config',
    'production'
);

// Set default namespace
$cluster->setDefaultNamespace('myapp');

// Create a deployment
$deployment = K8s::deployment($cluster)
    ->setName('web-app')
    ->setReplicas(3)
    ->setSelectors(['app' => 'web'])
    ->setTemplate([
        K8s::pod()
            ->setLabels(['app' => 'web'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
            ])
    ])
    ->create();

echo "Deployment created: {$deployment->getName()}\n";

// List all pods
$pods = $cluster->getAllPods();
echo "Found {$pods->count()} pods\n";

foreach ($pods as $pod) {
    echo "- {$pod->getName()}: {$pod->getPodPhase()->value}\n";
}
```

## See Also

- [K8sResource](/development/api-reference/k8s-resource) - Base resource class
- [K8s Facade](/development/api-reference/k8s-facade) - Helper facade class
- [Authentication](/guide/getting-started/authentication) - Authentication guide

---

*API reference for the KubernetesCluster class in cuppett/php-k8s fork*
