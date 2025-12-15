# Namespace

Namespaces provide a mechanism for isolating groups of resources within a single cluster.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$namespace = K8s::namespace($cluster)
    ->setName('production')
    ->setLabels(['environment' => 'production'])
    ->create();
```

## List Namespaces

```php
$namespaces = $cluster->getAllNamespaces();

foreach ($namespaces as $ns) {
    echo "Namespace: {$ns->getName()}\n";
}
```

## Delete Namespace

```php
$namespace->delete();
```

::: warning
Deleting a namespace will delete all resources within it.
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
