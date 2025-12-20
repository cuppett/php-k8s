# Deployment

Deployments provide declarative updates for Pods and ReplicaSets.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$deployment = K8s::deployment($cluster)
    ->setName('web-app')
    ->setNamespace('default')
    ->setReplicas(3)
    ->setSelectors(['app' => 'web'])
    ->setTemplate(
        K8s::pod()
            ->setLabels(['app' => 'web'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
            ])
    )
    ->create();
```

## Scale

```php
$deployment->scale(5);
```

## Update Image

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$patch = new JsonPatch();
$patch->replace('/spec/template/spec/containers/0/image', 'nginx:1.21');

$deployment->jsonPatch($patch);
```

## Get Status

```php
$deployment->refresh();

echo "Desired: {$deployment->getReplicas()}\n";
echo "Ready: {$deployment->getReadyReplicas()}\n";
echo "Available: {$deployment->getAvailableReplicas()}\n";
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
