# Version Upgrades

Guide for upgrading between versions of PHP K8s.

## Upgrading Within Fork

When upgrading to newer versions of this fork:

```bash
composer update renoki-co/php-k8s
```

Check the [Changelog](/project/changelog) for any breaking changes.

## Version Mapping

| Upstream Version | Fork Version | Notes |
|------------------|--------------|-------|
| 3.x | 3.x+ | Fork maintains compatibility where possible |
| 2.x | Not supported | PHP 8.2+ required |
| 1.x | Not supported | PHP 8.2+ required |

## Breaking Changes

### PHP Version Requirements

This fork requires PHP 8.2+. If you need PHP 8.0 or 8.1 support, use the upstream package.

### Enum Return Values

Methods that previously returned strings now return enums:

```php
// Before (strings)
$phase = $pod->getStatus()['phase'];  // "Running"

// After (enums)
use RenokiCo\PhpK8s\Enums\PodPhase;
$phase = $pod->getPodPhase();  // PodPhase::RUNNING
```

## See Also

- [Upstream to Fork Migration](/migration/upstream-to-fork) - Migrating from upstream
- [PHP 8.2+ Modernization](/migration/php-82-modernization) - Modern PHP features
- [Breaking Changes](/migration/breaking-changes) - All breaking changes

---

*Version upgrade guide for cuppett/php-k8s fork*
