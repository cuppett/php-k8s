# ConfigMap & Secrets

Managing configuration and secrets.

## ConfigMap Example

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create ConfigMap
$configMap = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('default')
    ->setData([
        'DATABASE_HOST' => 'mysql',
        'DATABASE_PORT' => '3306',
        'CACHE_DRIVER' => 'redis',
        'LOG_LEVEL' => 'info',
    ])
    ->create();

echo "Created ConfigMap: {$configMap->getName()}\n";
```

## Secret Example

```php
// Create Secret
$secret = K8s::secret($cluster)
    ->setName('app-secrets')
    ->setNamespace('default')
    ->setData('db-password', base64_encode('secret123'))
    ->setData('api-key', base64_encode('key456'))
    ->create();

echo "Created Secret: {$secret->getName()}\n";
```

## Use in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            // From ConfigMap
            ->addConfigMapRef('DATABASE_HOST', 'app-config', 'DATABASE_HOST')
            ->addConfigMapRef('DATABASE_PORT', 'app-config', 'DATABASE_PORT')
            // From Secret
            ->addSecretKeyRef('DB_PASSWORD', 'app-secrets', 'db-password')
            ->addSecretKeyRef('API_KEY', 'app-secrets', 'api-key')
    ])
    ->create();
```

## Mount as Volume

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            ->addMountedVolumes([
                K8s::volume()->name('config')->configMap('app-config')->mountTo('/etc/config'),
                K8s::volume()->name('secrets')->secret('app-secrets')->mountTo('/etc/secrets', true)
            ])
    ])
    ->addVolumes([
        K8s::volume()->name('config')->configMap('app-config'),
        K8s::volume()->name('secrets')->secret('app-secrets')
    ])
    ->create();
```

---

*ConfigMap and Secrets example for cuppett/php-k8s fork*
