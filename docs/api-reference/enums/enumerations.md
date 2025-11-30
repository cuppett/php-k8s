# Enumerations

PHP K8s uses enums for type-safe Kubernetes status values.

## Available Enums

### PodPhase

```php
enum PodPhase: string
{
    case PENDING = 'Pending';
    case RUNNING = 'Running';
    case SUCCEEDED = 'Succeeded';
    case FAILED = 'Failed';
    case UNKNOWN = 'Unknown';
}

// Usage
if ($pod->getPodPhase() === PodPhase::RUNNING) {
    echo "Pod is running";
}
```

### ServiceType

```php
enum ServiceType: string
{
    case CLUSTER_IP = 'ClusterIP';
    case NODE_PORT = 'NodePort';
    case LOAD_BALANCER = 'LoadBalancer';
    case EXTERNAL_NAME = 'ExternalName';
}
```

### RestartPolicy

```php
enum RestartPolicy: string
{
    case ALWAYS = 'Always';
    case ON_FAILURE = 'OnFailure';
    case NEVER = 'Never';
}
```

### Protocol

```php
enum Protocol: string
{
    case TCP = 'TCP';
    case UDP = 'UDP';
    case SCTP = 'SCTP';
}
```

### PullPolicy

```php
enum PullPolicy: string
{
    case ALWAYS = 'Always';
    case IF_NOT_PRESENT = 'IfNotPresent';
    case NEVER = 'Never';
}
```

### ContainerState

```php
enum ContainerState: string
{
    case WAITING = 'waiting';
    case RUNNING = 'running';
    case TERMINATED = 'terminated';
}
```

### EventType

```php
enum EventType: string
{
    case NORMAL = 'Normal';
    case WARNING = 'Warning';
}
```

### WatchEventType

```php
enum WatchEventType: string
{
    case ADDED = 'ADDED';
    case MODIFIED = 'MODIFIED';
    case DELETED = 'DELETED';
}
```

## Getting String Values

```php
$phase = $pod->getPodPhase();  // PodPhase enum

$phaseString = $phase->value;   // "Running" string
```

## See Also

- [PHP 8.2+ Modernization](/migration/php-82-modernization) - Enum usage guide

---

*Enumerations documentation for cuppett/php-k8s fork*
