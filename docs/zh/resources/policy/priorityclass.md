# PriorityClass

PriorityClasses define pod priority for scheduling decisions.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$priorityClass = K8s::priorityClass($cluster)
    ->setName('high-priority')
    ->setValue(1000000)
    ->setGlobalDefault(false)
    ->setDescription('High priority class for critical workloads')
    ->create();
```

::: info
PriorityClass is cluster-scoped (not namespaced).
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
