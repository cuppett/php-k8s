# Probe

Configure liveness, readiness, and startup probes for containers.

## HTTP Probe

```php
use RenokiCo\PhpK8s\K8s;

$probe = K8s::probe()
    ->http('/health', 8080, ['X-Custom-Header' => 'value'])
    ->setInitialDelaySeconds(10)
    ->setPeriodSeconds(30)
    ->setTimeoutSeconds(5)
    ->setSuccessThreshold(1)
    ->setFailureThreshold(3);

$container->setLivenessProbe($probe);
```

## TCP Probe

```php
$probe = K8s::probe()
    ->tcp(3306)
    ->setInitialDelaySeconds(5)
    ->setPeriodSeconds(10);

$container->setReadinessProbe($probe);
```

## Command Probe

```php
$probe = K8s::probe()
    ->command(['cat', '/tmp/healthy'])
    ->setInitialDelaySeconds(15)
    ->setPeriodSeconds(20);

$container->setStartupProbe($probe);
```

## Probe Configuration

- `setInitialDelaySeconds(int)` - Delay before first probe
- `setPeriodSeconds(int)` - Frequency of probes
- `setTimeoutSeconds(int)` - Probe timeout
- `setSuccessThreshold(int)` - Successes needed to be considered healthy
- `setFailureThreshold(int)` - Failures before marking unhealthy

## Example

```php
$container = K8s::container()
    ->setName('app')
    ->setImage('myapp:latest')
    ->setLivenessProbe(
        K8s::probe()
            ->http('/health', 8080)
            ->setInitialDelaySeconds(30)
            ->setPeriodSeconds(10)
    )
    ->setReadinessProbe(
        K8s::probe()
            ->http('/ready', 8080)
            ->setInitialDelaySeconds(5)
            ->setPeriodSeconds(5)
    );
```

## See Also

- [Container](/api-reference/instances/container) - Container configuration
- [Pod](/resources/workloads/pod) - Using probes in pods

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
