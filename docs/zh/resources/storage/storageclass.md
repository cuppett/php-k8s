# StorageClass

StorageClasses provide a way to describe different types of storage.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$storageClass = K8s::storageClass($cluster)
    ->setName('fast-ssd')
    ->setProvisioner('kubernetes.io/aws-ebs')
    ->setParameters([
        'type' => 'gp3',
        'iops' => '3000',
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
