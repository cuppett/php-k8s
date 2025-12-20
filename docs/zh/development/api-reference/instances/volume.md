# Volume

Configure volumes for pods.

## ConfigMap Volume

```php
use RenokiCo\PhpK8s\K8s;

$volume = K8s::volume()
    ->name('config-volume')
    ->configMap('app-config');

$pod->addVolumes([$volume]);
```

## Secret Volume

```php
$volume = K8s::volume()
    ->name('secret-volume')
    ->secret('app-secrets');

$pod->addVolumes([$volume]);
```

## EmptyDir Volume

```php
$volume = K8s::volume()
    ->name('cache-volume')
    ->emptyDir();

$pod->addVolumes([$volume]);
```

## PersistentVolumeClaim

```php
$volume = K8s::volume()
    ->name('data-volume')
    ->persistentVolumeClaim('data-pvc');

$pod->addVolumes([$volume]);
```

## HostPath Volume

```php
$volume = K8s::volume()
    ->name('host-volume')
    ->hostPath('/data', 'Directory');

$pod->addVolumes([$volume]);
```

## AWS EBS Volume

```php
$volume = K8s::volume()
    ->awsEbs('vol-12345', 'ext4');

$pod->addVolumes([$volume]);
```

## Mounting Volumes

```php
$volume = K8s::volume()
    ->name('config')
    ->configMap('app-config');

$container = K8s::container()
    ->setName('app')
    ->setImage('myapp:latest')
    ->addMountedVolumes([
        $volume->mountTo('/etc/config', true) // true = readOnly
    ]);

$pod->setContainers([$container])
    ->addVolumes([$volume]);
```

## See Also

- [Container](/development/api-reference/instances/container) - Mounting volumes in containers
- [Pod](/resources/workloads/pod) - Using volumes in pods

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
