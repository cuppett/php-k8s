# Contracts (Interfaces)

PHP K8s uses contracts (interfaces) to define resource capabilities.

## InteractsWithK8sCluster

The base contract that all resources must implement.

```php
interface InteractsWithK8sCluster
{
    public function create();
    public function update();
    public function delete();
}
```

## Watchable

Resources that can be watched for changes.

```php
interface Watchable
{
    public function watch(callable $callback);
    public function watchAll(callable $callback);
}
```

## Scalable

Resources that support scaling operations.

```php
interface Scalable
{
    public function scale(int $replicas);
}
```

## Loggable

Resources that provide log access (typically pods).

```php
interface Loggable
{
    public function logs(array $options = []);
}
```

## Executable

Resources that support exec operations (typically pods).

```php
interface Executable
{
    public function exec(array $command, ?string $container = null);
}
```

## Using Contracts

```php
use RenokiCo\PhpK8s\Contracts\{InteractsWithK8sCluster, Scalable};

class K8sDeployment extends K8sResource implements InteractsWithK8sCluster, Scalable
{
    // Implementation...
}
```

## See Also

- [Resource Model](/development/architecture/resource-model) - Architecture overview

---

*Contracts documentation for cuppett/php-k8s fork*
