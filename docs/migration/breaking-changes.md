# Breaking Changes

This page documents all breaking changes in the fork compared to upstream.

## PHP Version Requirement

**Breaking:** Minimum PHP version is now 8.2

```php
// composer.json
{
    "require": {
        "php": "^8.2"  // Was ^8.0 in upstream
    }
}
```

## Enum Return Types

**Breaking:** Status methods now return enums instead of strings

```php
// Before (upstream)
if ($pod->getStatus()['phase'] === 'Running') { }

// After (fork)
use RenokiCo\PhpK8s\Enums\PodPhase;
if ($pod->getPodPhase() === PodPhase::RUNNING) { }
```

### Affected Methods

- `getPodPhase()` - Returns `PodPhase` enum
- `getServiceType()` - Returns `ServiceType` enum
- `getRestartPolicy()` - Returns `RestartPolicy` enum
- `getProtocol()` - Returns `Protocol` enum
- `getPullPolicy()` - Returns `PullPolicy` enum

## Type Hints

**Breaking:** Methods now have strict type hints

```php
// Before (upstream)
public function setReplicas($replicas) { }

// After (fork)
public function setReplicas(int $replicas): self { }
```

## Migration Path

See the [Upstream to Fork Migration Guide](/migration/upstream-to-fork) for step-by-step migration instructions.

## See Also

- [Fork Differences](/project/fork-differences) - Complete comparison
- [PHP 8.2+ Modernization](/migration/php-82-modernization) - Modern PHP features

---

*Breaking changes documentation for cuppett/php-k8s fork*
