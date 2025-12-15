# ConfigMap

ConfigMaps store non-confidential configuration data in key-value pairs.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$configMap = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('default')
    ->setData([
        'DATABASE_HOST' => 'mysql.example.com',
        'DATABASE_PORT' => '3306',
        'CACHE_DRIVER' => 'redis',
    ])
    ->create();
```

## Update Data

```php
$configMap = $cluster->getConfigmapByName('app-config');

$configMap->addData('NEW_KEY', 'new_value')->update();
```

## Use in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            ->addConfigMapRef('DATABASE_HOST', 'app-config', 'DATABASE_HOST')
            ->addConfigMapRef('DATABASE_PORT', 'app-config', 'DATABASE_PORT')
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
