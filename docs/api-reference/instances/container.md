# Container Instance

Container instances represent container specifications in pods.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('nginx')
    ->setImage('nginx:latest')
    ->setPorts([
        K8s::containerPort()->setContainerPort(80)
    ])
    ->setCommand(['/bin/sh'])
    ->setArgs(['-c', 'nginx -g "daemon off;"']);
```

## Environment Variables

### Direct Values

```php
$container->setEnv([
    'DATABASE_HOST' => 'mysql',
    'DATABASE_PORT' => '3306',
]);

// Add single variable
$container->addEnv('API_KEY', 'secret-value');
```

### From Secret

```php
$container->addSecretKeyRef('DATABASE_PASSWORD', 'db-secret', 'password');

// Multiple from same secret
$container->addSecretKeyRefs([
    'DB_USER' => ['db-secret', 'username'],
    'DB_PASS' => ['db-secret', 'password'],
]);
```

### From ConfigMap

```php
$container->addConfigMapRef('APP_CONFIG', 'app-configmap', 'config-key');

// Multiple from same configmap
$container->addConfigMapRefs([
    'APP_NAME' => ['app-configmap', 'name'],
    'APP_ENV' => ['app-configmap', 'environment'],
]);
```

### From Field

```php
$container->addFieldRef('NODE_NAME', 'spec.nodeName');
$container->addFieldRef('POD_NAME', 'metadata.name');
```

## Ports

```php
// Array format
$container->setPorts([
    ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
    ['name' => 'https', 'protocol' => 'TCP', 'containerPort' => 443],
]);

// Fluent API
$container->addPort(8080, 'TCP', 'metrics');
```

## Probes

### Liveness Probe

```php
$container->setLivenessProbe(
    K8s::probe()
        ->http('/health', 8080)
        ->setInitialDelaySeconds(30)
        ->setPeriodSeconds(10)
        ->setTimeoutSeconds(5)
        ->setFailureThreshold(3)
);
```

### Readiness Probe

```php
$container->setReadinessProbe(
    K8s::probe()
        ->http('/ready', 8080)
        ->setInitialDelaySeconds(5)
        ->setPeriodSeconds(5)
);
```

### Startup Probe

```php
$container->setStartupProbe(
    K8s::probe()
        ->tcp(3306)
        ->setInitialDelaySeconds(10)
        ->setFailureThreshold(30)
);
```

## Resource Limits

```php
// CPU
$container->minCpu('100m')->maxCpu('500m');

// Memory
$container->minMemory('128Mi')->maxMemory('512Mi');

// Combined
$container
    ->minCpu('100m')->maxCpu('1')
    ->minMemory('256Mi')->maxMemory('1Gi');
```

## Volume Mounts

```php
$volume = K8s::volume()
    ->name('data')
    ->persistentVolumeClaim('data-pvc');

$container->addMountedVolumes([
    $volume->mountTo('/var/lib/data')
]);
```

## Complete Example

```php
$container = K8s::container()
    ->setName('app')
    ->setImage('myapp:v1.0')
    ->setPorts([
        K8s::containerPort()->setContainerPort(8080)->setName('http')
    ])
    ->setEnv([
        'APP_ENV' => 'production',
    ])
    ->addSecretKeyRef('DB_PASSWORD', 'db-secret', 'password')
    ->addConfigMapRef('APP_CONFIG', 'app-config', 'config.json')
    ->setLivenessProbe(
        K8s::probe()->http('/health', 8080)->setInitialDelaySeconds(30)
    )
    ->setReadinessProbe(
        K8s::probe()->http('/ready', 8080)->setInitialDelaySeconds(5)
    )
    ->minCpu('100m')->maxCpu('500m')
    ->minMemory('256Mi')->maxMemory('512Mi');
```

## See Also

- [Pod](/resources/workloads/pod) - Using containers in pods
- [Probe](/api-reference/instances/probe) - Probe configuration
- [Volume](/api-reference/instances/volume) - Volume configuration

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
