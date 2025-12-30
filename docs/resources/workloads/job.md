# Job

Jobs create one or more pods and ensure they successfully terminate.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$job = K8s::job($cluster)
    ->setName('backup-job')
    ->setNamespace('default')
    ->setTemplate(
        K8s::pod()
            ->setContainers([
                K8s::container()
                    ->setName('backup')
                    ->setImage('backup-tool:latest')
                    ->setCommand(['./backup.sh'])
            ])
    )
    ->create();
```

## Get Job Status

```php
$job->refresh();

echo "Succeeded: {$job->getSucceeded()}\n";
echo "Failed: {$job->getFailed()}\n";
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
