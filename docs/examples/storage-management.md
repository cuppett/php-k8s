# Storage Management

Examples for managing persistent storage.

## PersistentVolumeClaim

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$pvc = K8s::persistentVolumeClaim($cluster)
    ->setName('data-pvc')
    ->setNamespace('default')
    ->setCapacity('10Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('standard')
    ->create();

echo "PVC created: {$pvc->getName()}\n";
```

## Use PVC in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            ->addMountedVolumes([
                K8s::volume()
                    ->name('data')
                    ->persistentVolumeClaim('data-pvc')
                    ->mountTo('/var/lib/data')
            ])
    ])
    ->addVolumes([
        K8s::volume()->name('data')->persistentVolumeClaim('data-pvc')
    ])
    ->create();
```

---

*Storage management example for cuppett/php-k8s fork*
