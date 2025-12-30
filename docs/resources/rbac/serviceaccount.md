# ServiceAccount

ServiceAccounts provide an identity for processes running in pods.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$sa = K8s::serviceAccount($cluster)
    ->setName('app-sa')
    ->setNamespace('default')
    ->create();
```

## Use in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setServiceAccountName('app-sa')
    ->setContainers([...])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
