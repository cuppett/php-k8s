# PHP 8.2+ Modernization Guide

This guide explains the modern PHP features now used in php-k8s and how to leverage them in your code.

## Overview

The library now requires PHP 8.2+ and uses modern features:

- **Enums** for type-safe constants
- **Match expressions** for cleaner control flow
- **Comprehensive type hints** for better IDE support
- **Union types** for flexible parameters
- **Property types** for stricter validation

All features are compatible with PHP 8.2+, ensuring broad compatibility while delivering modern developer experience.

## Enums

### Operation Enum

Replaces string constants for cluster operations:

```php
use RenokiCo\PhpK8s\Enums\Operation;

// All available operations
Operation::GET           // 'get'
Operation::CREATE        // 'create'
Operation::REPLACE       // 'replace'
Operation::DELETE        // 'delete'
Operation::LOG           // 'logs'
Operation::WATCH         // 'watch'
Operation::WATCH_LOGS    // 'watch_logs'
Operation::EXEC          // 'exec'
Operation::ATTACH        // 'attach'
Operation::APPLY         // 'apply'
Operation::JSON_PATCH    // 'json_patch'
Operation::JSON_MERGE_PATCH  // 'json_merge_patch'

// Helper methods
$operation = Operation::EXEC;
$method = $operation->httpMethod();      // Returns 'POST'
$usesWs = $operation->usesWebSocket();   // Returns true

// Get string value
$stringValue = Operation::GET->value;    // Returns 'get'
```

### PodPhase Enum

Type-safe pod lifecycle phases:

```php
use RenokiCo\PhpK8s\Enums\PodPhase;

$phase = $pod->getPodPhase();  // Returns PodPhase enum

// Available phases
PodPhase::PENDING
PodPhase::RUNNING
PodPhase::SUCCEEDED
PodPhase::FAILED
PodPhase::UNKNOWN

// Helper methods
$phase->isTerminal();    // true for SUCCEEDED or FAILED
$phase->isActive();      // true for RUNNING or PENDING
$phase->isSuccessful();  // true for SUCCEEDED

// Comparison
if ($phase === PodPhase::RUNNING) {
    echo "Pod is running";
}

// Or use existing helpers
if ($pod->isRunning()) {
    echo "Pod is running";
}
```

### RestartPolicy Enum

Type-safe restart policies:

```php
use RenokiCo\PhpK8s\Enums\RestartPolicy;

// Set restart policy
$pod->setRestartPolicy(RestartPolicy::ON_FAILURE);

// Or use convenience methods
$pod->restartOnFailure();  // Sets to ON_FAILURE
$pod->neverRestart();      // Sets to NEVER

// Get restart policy
$policy = $pod->getRestartPolicy();  // Returns RestartPolicy enum

// Available policies
RestartPolicy::ALWAYS
RestartPolicy::ON_FAILURE
RestartPolicy::NEVER

// Helper methods
$policy->allowsRestarts();   // false for NEVER
$policy->onlyOnFailure();    // true for ON_FAILURE
```

### ServiceType Enum

Type-safe service types:

```php
use RenokiCo\PhpK8s\Enums\ServiceType;

// Set service type
$service->setType(ServiceType::LOAD_BALANCER);

// Get service type
$type = $service->getType();  // Returns ServiceType enum

// Available types
ServiceType::CLUSTER_IP
ServiceType::NODE_PORT
ServiceType::LOAD_BALANCER
ServiceType::EXTERNAL_NAME

// Helper methods
$type->isExternallyAccessible();  // true for NODE_PORT or LOAD_BALANCER
$type->createsLoadBalancer();     // true for LOAD_BALANCER

// Check if externally accessible
if ($service->isExternallyAccessible()) {
    echo "Service can be accessed from outside";
}
```

### Protocol Enum

Type-safe network protocols:

```php
use RenokiCo\PhpK8s\Enums\Protocol;

// Available protocols
Protocol::TCP
Protocol::UDP
Protocol::SCTP

// Helper methods
$protocol = Protocol::TCP;
$protocol->isConnectionOriented();  // Returns true

// Usage in port definitions
$port = [
    'port' => 80,
    'protocol' => Protocol::TCP->value,  // 'TCP'
    'targetPort' => 8080,
];
```

### ConditionStatus Enum

Type-safe condition statuses:

```php
use RenokiCo\PhpK8s\Enums\ConditionStatus;

// Available statuses
ConditionStatus::TRUE
ConditionStatus::FALSE
ConditionStatus::UNKNOWN

// Helper methods
$status = ConditionStatus::TRUE;
$status->isTrue();    // Returns true
$status->isFalse();   // Returns false
$status->isKnown();   // Returns true (not UNKNOWN)
```

### AccessMode Enum

Type-safe persistent volume access modes:

```php
use RenokiCo\PhpK8s\Enums\AccessMode;

// Available modes
AccessMode::READ_WRITE_ONCE      // ReadWriteOnce (RWO)
AccessMode::READ_ONLY_MANY       // ReadOnlyMany (ROX)
AccessMode::READ_WRITE_MANY      // ReadWriteMany (RWX)
AccessMode::READ_WRITE_ONCE_POD  // ReadWriteOncePod (RWOP)

// Helper methods
$mode = AccessMode::READ_WRITE_ONCE;
$mode->allowsWrite();     // Returns true
$mode->allowsMultiple();  // Returns false
$mode->isPodScoped();     // Returns false
```

### PullPolicy Enum

Type-safe image pull policies:

```php
use RenokiCo\PhpK8s\Enums\PullPolicy;

// Available policies
PullPolicy::ALWAYS
PullPolicy::NEVER
PullPolicy::IF_NOT_PRESENT

// Helper methods
$policy = PullPolicy::IF_NOT_PRESENT;
$policy->alwaysPulls();    // Returns false
$policy->allowsCached();   // Returns true
```

## Type Hints

### Comprehensive Type Coverage

All methods now have proper type hints:

```php
// Before
public function setName($name)
{
    return $this->setAttribute('metadata.name', $name);
}

// After
public function setName(string $name): static
{
    return $this->setAttribute('metadata.name', $name);
}
```

### Union Types

Methods accept multiple types where appropriate:

```php
// runOperation accepts Operation enum or string
public function runOperation(
    Operation|string $operation,
    string $path,
    string|null|Closure $payload = '',
    array $query = ['pretty' => 1]
): mixed
```

### Return Type Benefits

- **IDE Autocomplete:** Better code completion
- **Static Analysis:** Psalm/PHPStan catch errors
- **Self-Documenting:** Types show intent clearly
- **Runtime Checks:** PHP validates return types

## Best Practices

### 1. Use Enums for Type Safety

**Recommended:**
```php
use RenokiCo\PhpK8s\Enums\ServiceType;

$service->setType(ServiceType::LOAD_BALANCER);
```

**Avoid:**
```php
// Stringly-typed (prone to typos)
$service->setSpec('type', 'LoadBalancer');
```

### 2. Leverage Helper Methods

Enums provide semantic helper methods:

```php
// Instead of:
if ($pod->getPhase() === 'Succeeded' || $pod->getPhase() === 'Failed') {
    // ...
}

// Use:
if ($pod->isTerminal()) {
    // ...
}
```

### 3. Type Hints in Your Code

When extending or using the library:

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Enums\PodPhase;

function waitForPodReady(K8sPod $pod): bool
{
    while ($pod->getPodPhase() !== PodPhase::RUNNING) {
        sleep(1);
        $pod->refresh();

        if ($pod->isTerminal()) {
            return false;  // Pod failed
        }
    }

    return true;
}
```

## IDE Configuration

### PHPStorm/IntelliJ

Enums are automatically recognized. Enable inspections:

- Settings → PHP → Quality Tools → Psalm
- Settings → Editor → Inspections → PHP → Type checks

### VS Code

Install extensions for better enum support:

- PHP Intelephense
- Psalm
- PHP Namespace Resolver

## Performance

### Enum Performance

Enums are backed by strings (no performance penalty):

```php
// These are equivalent in performance:
$operation = Operation::GET;           // Enum (recommended)
$operation = 'get';                    // String (old way)

// Enum provides type safety with zero runtime cost
```

### Match Expression Performance

Match expressions are compiled similarly to switch statements (no performance difference):

```php
// Match (new)
return match ($operation) {
    Operation::GET => $this->handleGet(),
    Operation::CREATE => $this->handleCreate(),
};

// Switch (old) - same performance
switch ($operation) {
    case 'get':
        return $this->handleGet();
    case 'create':
        return $this->handleCreate();
}
```

## Testing

### Unit Tests

Enums work seamlessly in tests:

```php
use RenokiCo\PhpK8s\Enums\RestartPolicy;

public function test_restart_policy()
{
    $pod = K8s::pod()->neverRestart();

    $this->assertEquals(RestartPolicy::NEVER, $pod->getRestartPolicy());
    $this->assertFalse($pod->getRestartPolicy()->allowsRestarts());
}
```

### Mocking

Mock enum returns in tests:

```php
$mock = $this->createMock(K8sPod::class);
$mock->method('getPodPhase')
    ->willReturn(PodPhase::RUNNING);
```

## Further Reading

- [PHP 8.1 Enums](https://www.php.net/manual/en/language.enumerations.php)
- [PHP 8.0 Match Expression](https://www.php.net/manual/en/control-structures.match.php)
- [PHP 8.0 Union Types](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.union)
- [PHP 8.0 Mixed Type](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.mixed)
