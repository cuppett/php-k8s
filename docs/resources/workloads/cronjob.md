# CronJob

CronJobs create jobs on a repeating schedule.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$cronJob = K8s::cronJob($cluster)
    ->setName('nightly-backup')
    ->setNamespace('default')
    ->setSchedule('0 2 * * *')  // 2 AM daily
    ->setJobTemplate(
        K8s::job()
            ->setTemplate(
                K8s::pod()
                    ->setContainers([
                        K8s::container()
                            ->setName('backup')
                            ->setImage('backup-tool:latest')
                    ])
            )
    )
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
