# PersistentVolume

PersistentVolumes are storage resources in the cluster.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$pv = K8s::persistentVolume($cluster)
    ->setName('data-pv')
    ->setCapacity('10Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('standard')
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
