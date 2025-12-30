# Trait Composition

How PHP K8s uses traits to compose resource functionality.

## Overview

PHP K8s uses trait-based composition instead of deep inheritance hierarchies. This allows resources to mix and match functionality as needed.

## Pattern

```php
class K8sPod extends K8sResource
{
    use HasSpec;
    use HasStatus;
    use HasMetadata;
    use HasSelector;

    // Resource-specific methods
}
```

## Available Traits

See [Resource Traits](/development/api-reference/traits/resource-traits) for complete list.

## Benefits

- **Flexibility** - Resources only include traits they need
- **Reusability** - Traits can be used across different resources
- **Maintainability** - Changes to traits affect all resources using them
- **No Diamond Problem** - Avoid multiple inheritance issues

## See Also

- [Resource Model](/development/architecture/resource-model) - Overall architecture
- [Resource Traits](/development/api-reference/traits/resource-traits) - Available traits

---

*Trait composition documentation for cuppett/php-k8s fork*
