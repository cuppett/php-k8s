# Migrating from Upstream to Fork

This guide helps you migrate from `renoki-co/php-k8s` (upstream) to `cuppett/php-k8s` (this fork).

## Should You Migrate?

Consider migrating if you:

- ✅ Are using or plan to use PHP 8.2+
- ✅ Want type-safe enums for Kubernetes states
- ✅ Need the latest Kubernetes version support
- ✅ Value comprehensive documentation
- ✅ Want active maintenance and updates

Stay with upstream if you:

- ❌ Need PHP 8.0 or 8.1 support
- ❌ Prefer string-based status values
- ❌ Have deeply integrated with upstream-specific features
- ❌ Want the "official" package from the original author

## Prerequisites

- PHP 8.2 or higher installed
- Composer
- Existing project using `renoki-co/php-k8s`

## Migration Steps

### 1. Update Composer

```bash
# Remove upstream package
composer remove renoki-co/php-k8s

# Add fork package
composer require renoki-co/php-k8s  # Same package name, different source
```

::: tip Package Name
The fork maintains the same package name for easier migration. Configure your composer.json to use the fork repository if needed.
:::

### 2. Update PHP Version

Ensure your project uses PHP 8.2+:

```json
{
    "require": {
        "php": "^8.2"
    }
}
```

### 3. Handle Breaking Changes

#### Enum Return Values

**Upstream** (strings):
```php
// Returns string
$phase = $pod->getStatus()['phase'];  // "Running"

if ($phase === 'Running') {
    // ...
}
```

**Fork** (enums):
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

// Returns enum
$phase = $pod->getPodPhase();  // PodPhase::RUNNING

if ($phase === PodPhase::RUNNING) {
    // Type-safe comparison
}

// Get string value
$phaseString = $phase->value;  // "Running"
```

#### Migration Pattern for Status Checks

```php
// Before (upstream)
if ($pod->getStatus()['phase'] === 'Running') { }

// After (fork)
use RenokiCo\PhpK8s\Enums\PodPhase;

if ($pod->getPodPhase() === PodPhase::RUNNING) { }
```

### 4. Update Enum Usage

Replace string comparisons with enum comparisons:

```php
use RenokiCo\PhpK8s\Enums\{
    PodPhase,
    RestartPolicy,
    ServiceType,
    Protocol,
    PullPolicy
};

// Pod phases
$pod->getPodPhase() === PodPhase::RUNNING
$pod->getPodPhase() === PodPhase::PENDING
$pod->getPodPhase() === PodPhase::FAILED

// Service types
$service->getType() === ServiceType::CLUSTER_IP
$service->getType() === ServiceType::LOAD_BALANCER

// Restart policies
$pod->getRestartPolicy() === RestartPolicy::ALWAYS
```

### 5. Update Type Hints

Add type hints for better IDE support:

```php
// Before
function deployApp($cluster, $name, $replicas) {
    // ...
}

// After
use RenokiCo\PhpK8s\KubernetesCluster;

function deployApp(KubernetesCluster $cluster, string $name, int $replicas): void {
    // ...
}
```

## Common Migration Scenarios

### Scenario 1: Simple Pod Status Check

**Before:**
```php
$pod = $cluster->getPodByName('my-pod');

if ($pod->getStatus()['phase'] === 'Running') {
    echo "Pod is running";
}
```

**After:**
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

$pod = $cluster->getPodByName('my-pod');

if ($pod->getPodPhase() === PodPhase::RUNNING) {
    echo "Pod is running";
}
```

### Scenario 2: Service Type Checking

**Before:**
```php
$service = $cluster->getServiceByName('api');

if ($service->getType() === 'LoadBalancer') {
    $ip = $service->getLoadBalancerIp();
}
```

**After:**
```php
use RenokiCo\PhpK8s\Enums\ServiceType;

$service = $cluster->getServiceByName('api');

if ($service->getType() === ServiceType::LOAD_BALANCER) {
    $ip = $service->getLoadBalancerIp();
}
```

### Scenario 3: Match Expression Usage

**Before:**
```php
$action = '';

switch ($pod->getStatus()['phase']) {
    case 'Running':
        $action = 'monitor';
        break;
    case 'Pending':
        $action = 'wait';
        break;
    case 'Failed':
        $action = 'alert';
        break;
    default:
        $action = 'investigate';
}
```

**After:**
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

$action = match ($pod->getPodPhase()) {
    PodPhase::RUNNING => 'monitor',
    PodPhase::PENDING => 'wait',
    PodPhase::FAILED => 'alert',
    default => 'investigate',
};
```

## Compatibility Layer (Temporary)

If you need time to migrate gradually, create a compatibility layer:

```php
// In your codebase
class PodHelper
{
    public static function getPhaseString(\RenokiCo\PhpK8s\Kinds\K8sPod $pod): string
    {
        return $pod->getPodPhase()->value;
    }

    public static function isRunning(\RenokiCo\PhpK8s\Kinds\K8sPod $pod): bool
    {
        return $pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING;
    }
}

// Use in your code
if (PodHelper::isRunning($pod)) {
    // ...
}
```

## Testing After Migration

### 1. Update Tests

```php
// Before
$this->assertEquals('Running', $pod->getStatus()['phase']);

// After
use RenokiCo\PhpK8s\Enums\PodPhase;

$this->assertSame(PodPhase::RUNNING, $pod->getPodPhase());
```

### 2. Run Your Test Suite

```bash
vendor/bin/phpunit
```

### 3. Check Static Analysis

```bash
# If using Psalm
vendor/bin/psalm

# If using PHPStan
vendor/bin/phpstan analyse
```

## Benefits After Migration

- ✅ **Type Safety** - Catch errors at compile time
- ✅ **IDE Support** - Better autocomplete and refactoring
- ✅ **Modern PHP** - Use latest language features
- ✅ **Better Docs** - Comprehensive documentation at php-k8s.cuppett.dev
- ✅ **Active Maintenance** - Regular updates and bug fixes

## Gradual Migration Strategy

You can migrate gradually:

1. **Week 1**: Update composer, ensure project works
2. **Week 2**: Update pod-related code to use enums
3. **Week 3**: Update service and deployment code
4. **Week 4**: Complete migration, remove compatibility layer

## Rollback Plan

If you need to rollback:

```bash
# Remove fork
composer remove renoki-co/php-k8s

# Reinstall upstream
composer require renoki-co/php-k8s:^3.0

# Revert code changes
git checkout main -- .
```

## Common Issues

### Issue: Type Errors

**Problem:**
```
TypeError: Return value must be of type string, enum returned
```

**Solution:**
Use `->value` to get string from enum:
```php
$phaseString = $pod->getPodPhase()->value;
```

### Issue: Comparison Failures

**Problem:**
```php
if ($pod->getPodPhase() === 'Running') {  // Always false
    // ...
}
```

**Solution:**
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

if ($pod->getPodPhase() === PodPhase::RUNNING) {
    // ...
}
```

## Getting Help

- **Documentation**: https://php-k8s.cuppett.dev
- **Issues**: https://github.com/cuppett/php-k8s/issues
- **Discussions**: https://github.com/cuppett/php-k8s/discussions

## Next Steps

- [Fork Differences](/project/fork-differences) - Detailed comparison
- [PHP 8.2+ Modernization](/development/migration/php-82-modernization) - Modern PHP features
- [Examples](/examples/basic-crud) - See fork in action

---

*Migration guide for switching from renoki-co/php-k8s to cuppett/php-k8s fork*
