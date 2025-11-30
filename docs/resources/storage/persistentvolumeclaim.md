# PersistentVolumeClaim

PersistentVolumeClaims request storage resources.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$pvc = K8s::persistentVolumeClaim($cluster)
    ->setName('data-pvc')
    ->setNamespace('default')
    ->setCapacity('10Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('standard')
    ->create();
```

## Use in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            ->addMountedVolumes([
                K8s::volume()->name('data')->persistentVolumeClaim('data-pvc')->mountTo('/data')
            ])
    ])
    ->addVolumes([
        K8s::volume()->name('data')->persistentVolumeClaim('data-pvc')
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
