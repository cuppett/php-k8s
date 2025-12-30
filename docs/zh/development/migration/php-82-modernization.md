# PHP 8.2+ Modernization

This fork leverages PHP 8.2+ features for improved type safety, performance, and developer experience.

## PHP 8.2+ Features Used

### Enumerations (Enums)

Replace magic strings with type-safe enums:

```php
use RenokiCo\PhpK8s\Enums\PodPhase;
use RenokiCo\PhpK8s\Enums\ServiceType;
use RenokiCo\PhpK8s\Enums\RestartPolicy;

// Before (strings)
if ($pod->getStatus()['phase'] === 'Running') { }

// After (enums)
if ($pod->getPodPhase() === PodPhase::RUNNING) { }
```

### Readonly Properties

Immutable class properties:

```php
class K8sResource
{
    public readonly string $apiVersion;
    public readonly string $kind;
}
```

### Match Expressions

Cleaner conditional logic:

```php
$action = match ($pod->getPodPhase()) {
    PodPhase::RUNNING => 'monitor',
    PodPhase::PENDING => 'wait',
    PodPhase::FAILED => 'alert',
    PodPhase::SUCCEEDED => 'cleanup',
    default => 'investigate',
};
```

### Nullable Types & Union Types

Better type safety:

```php
function getPod(string $name, ?string $namespace = null): ?K8sPod
{
    // ...
}

function handleResult(K8sPod|array $result): void
{
    // ...
}
```

## Available Enums

### PodPhase

```php
namespace RenokiCo\PhpK8s\Enums;

enum PodPhase: string
{
    case PENDING = 'Pending';
    case RUNNING = 'Running';
    case SUCCEEDED = 'Succeeded';
    case FAILED = 'Failed';
    case UNKNOWN = 'Unknown';
}
```

Usage:
```php
$phase = $pod->getPodPhase();

if ($phase === PodPhase::RUNNING) {
    echo "Pod is active";
}

// Get string value
echo $phase->value;  // "Running"
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

## Migration Examples

### Pod Status Checking

```php
// Before
function isPodReady($pod): bool
{
    return $pod->getStatus()['phase'] === 'Running';
}

// After
use RenokiCo\PhpK8s\Enums\PodPhase;

function isPodReady(K8sPod $pod): bool
{
    return $pod->getPodPhase() === PodPhase::RUNNING;
}
```

### Service Type Handling

```php
// Before
function getServiceUrl($service): ?string
{
    if ($service->getType() === 'LoadBalancer') {
        return $service->getLoadBalancerIp();
    }
    return null;
}

// After
use RenokiCo\PhpK8s\Enums\ServiceType;

function getServiceUrl(K8sService $service): ?string
{
    return match ($service->getType()) {
        ServiceType::LOAD_BALANCER => $service->getLoadBalancerIp(),
        ServiceType::NODE_PORT => $service->getNodePortUrl(),
        default => null,
    };
}
```

### Watch Event Processing

```php
// Before
$cluster->pod()->watchAll(function ($type, $pod) {
    if ($type === 'ADDED') {
        echo "New pod created";
    } elseif ($type === 'MODIFIED') {
        echo "Pod updated";
    } elseif ($type === 'DELETED') {
        echo "Pod deleted";
    }
});

// After
use RenokiCo\PhpK8s\Enums\WatchEventType;

$cluster->pod()->watchAll(function ($type, $pod) {
    match ($type) {
        WatchEventType::ADDED->value => print("New pod created"),
        WatchEventType::MODIFIED->value => print("Pod updated"),
        WatchEventType::DELETED->value => print("Pod deleted"),
    };
});
```

## Type Hints and Return Types

All methods now have proper type hints:

```php
// Before
public function setReplicas($replicas)
{
    return $this->setAttribute('spec.replicas', $replicas);
}

// After
public function setReplicas(int $replicas): self
{
    return $this->setAttribute('spec.replicas', $replicas);
}
```

## Benefits

### 1. IDE Support

Better autocomplete and type checking:

```php
$pod->getPodPhase()->  // IDE shows enum methods and cases
```

### 2. Compile-Time Errors

Catch mistakes early:

```php
// This won't work (caught by IDE/static analysis)
if ($pod->getPodPhase() === 'Running') {  // Type mismatch
    // ...
}

// Correct
if ($pod->getPodPhase() === PodPhase::RUNNING) {  // ✓
    // ...
}
```

### 3. Refactoring Safety

Renaming becomes safer:

```php
// All usages of PodPhase::RUNNING can be found and updated
// String 'Running' could be anywhere in the codebase
```

### 4. Performance

Enums are more performant than strings for comparisons.

## Backward Compatibility

### Getting String Values

If you need the string value:

```php
$phase = $pod->getPodPhase();
$phaseString = $phase->value;  // "Running"

// Or inline
$phaseString = $pod->getPodPhase()->value;
```

### Working with Legacy Code

If integrating with code expecting strings:

```php
function legacyFunction(string $phase): void
{
    // Expects string
}

// Convert enum to string
legacyFunction($pod->getPodPhase()->value);
```

## Static Analysis

Run Psalm to catch type errors:

```bash
vendor/bin/psalm
```

Example output:
```
ERROR: InvalidArgument - Expected PodPhase, string provided
if ($pod->getPodPhase() === 'Running') {
```

## Recommended Practices

1. **Import enums** at the top of your files:
   ```php
   use RenokiCo\PhpK8s\Enums\{PodPhase, ServiceType, RestartPolicy};
   ```

2. **Use match expressions** for complex conditions:
   ```php
   $action = match ($pod->getPodPhase()) {
       PodPhase::RUNNING => 'monitor',
       PodPhase::FAILED => 'alert',
       default => 'wait',
   };
   ```

3. **Add type hints** to your functions:
   ```php
   function deployPod(KubernetesCluster $cluster, string $name): K8sPod
   {
       // ...
   }
   ```

4. **Use readonly** for immutable data:
   ```php
   readonly class DeploymentConfig
   {
       public function __construct(
           public string $name,
           public int $replicas,
           public string $image,
       ) {}
   }
   ```

## See Also

- [Upstream to Fork Migration](/development/migration/upstream-to-fork) - Migrating from upstream
- [Fork Differences](/project/fork-differences) - All differences explained
- [Examples](/examples/basic-crud) - See modern PHP in action

---

*PHP 8.2+ modernization guide for cuppett/php-k8s fork*
