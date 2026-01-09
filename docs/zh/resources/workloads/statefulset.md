# StatefulSet

StatefulSets manage stateful applications with stable network identities and persistent storage.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$statefulSet = K8s::statefulSet($cluster)
    ->setName('mysql')
    ->setNamespace('databases')
    ->setReplicas(3)
    ->setServiceName('mysql')
    ->setSelectors(['app' => 'mysql'])
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'mysql'])
            ->setContainers([
                K8s::container()
                    ->setName('mysql')
                    ->setImage('mysql:5.7')
            ])
    )
    ->create();
```

## Scale StatefulSet

```php
$statefulSet->scale(5);
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
