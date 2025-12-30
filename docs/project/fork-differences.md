# Fork Differences

This page details the differences between the `cuppett/php-k8s` fork and the upstream `renoki-co/php-k8s` project.

## Why This Fork Exists

This fork was created to:

1. **Continue Active Development** - Maintain active development and support
2. **PHP 8.2+ Modernization** - Leverage modern PHP features
3. **Enhanced Type Safety** - Utilize enums and strict type hints
4. **Community Support** - Provide ongoing maintenance and updates

## Key Differences

### PHP Version Support

| Aspect | Upstream | This Fork |
|--------|----------|-----------|
| Minimum PHP | 8.0+ | 8.2+ |
| Recommended PHP | 8.1+ | 8.3+ |
| PHP 8.4 Support | Partial | Full |

### Modern PHP Features

#### Enums

**Upstream** (strings):
```php
$pod->getStatus()['phase']; // Returns string "Running"
```

**This Fork** (enums):
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

$pod->getPodPhase(); // Returns PodPhase enum
$pod->getPodPhase() === PodPhase::RUNNING; // Type-safe comparison
$pod->getPodPhase()->value; // Get string value
```

Available enums in this fork:
- `PodPhase` - Running, Pending, Succeeded, Failed, Unknown
- `RestartPolicy` - Always, OnFailure, Never
- `ServiceType` - ClusterIP, NodePort, LoadBalancer, ExternalName
- `Protocol` - TCP, UDP, SCTP
- `PullPolicy` - Always, IfNotPresent, Never
- `ContainerState` - Waiting, Running, Terminated
- `EventType` - Normal, Warning
- `WatchEventType` - Added, Modified, Deleted

#### Readonly Properties

**This Fork**:
```php
class K8sResource
{
    public readonly string $apiVersion;
    public readonly string $kind;
}
```

#### Match Expressions

**This Fork**:
```php
$action = match ($pod->getPodPhase()) {
    PodPhase::RUNNING => 'monitor',
    PodPhase::PENDING => 'wait',
    PodPhase::FAILED => 'alert',
    default => 'investigate',
};
```

### Enhanced Type Hints

**Upstream**:
```php
public function setReplicas($replicas)
{
    return $this->setAttribute('spec.replicas', $replicas);
}
```

**This Fork**:
```php
public function setReplicas(int $replicas): self
{
    return $this->setAttribute('spec.replicas', $replicas);
}
```

### Additional Resource Support

This fork may include additional Kubernetes resource types:

- ✅ All upstream resources
- ✅ PriorityClass
- ✅ EndpointSlice
- ✅ VerticalPodAutoscaler
- ✅ ValidatingWebhookConfiguration
- ✅ MutatingWebhookConfiguration

### Documentation Improvements

| Feature | Upstream | This Fork |
|---------|----------|-----------|
| Documentation Site | GitBook | VitePress (self-hosted) |
| Search | GitBook search | Local search (no external service) |
| Examples | Basic | Comprehensive with real-world patterns |
| API Reference | Partial | Complete with all classes/traits/enums |
| Migration Guides | Limited | Detailed version-by-version guides |

### Testing Infrastructure

**This Fork**:
- Tests against Kubernetes 1.32.9, 1.33.5, 1.34.1
- Automated testing with GitHub Actions
- Integration tests with Minikube
- VPA, Gateway API, and Sealed Secrets CRD testing

### Breaking Changes from Upstream

::: warning Breaking Changes
If migrating from upstream to this fork, be aware of these breaking changes:
:::

1. **PHP Version** - Requires PHP 8.2+ (upstream supports 8.0+)
2. **Enum Return Values** - Methods like `getPodPhase()` return enums instead of strings
3. **Strict Types** - More strict type hints may require code updates
4. **Method Signatures** - Some methods have enhanced type declarations

### Migration Path from Upstream

See the [Upstream to Fork Migration Guide](/development/migration/upstream-to-fork) for detailed migration instructions.

## What's the Same

To maintain compatibility and ease migration:

- ✅ Core API structure and design patterns
- ✅ Method names and basic signatures
- ✅ Resource attribute paths
- ✅ YAML import/export functionality
- ✅ WebSocket operations (exec, attach, watch)
- ✅ Patch operations (JSON Patch, JSON Merge Patch)
- ✅ Laravel integration approach

## Version Mapping

| Upstream Version | Fork Version | Notes |
|------------------|--------------|-------|
| 3.x | 3.x+ | Fork branched from upstream 3.x |
| 2.x | Not supported | PHP 8.2+ required |
| 1.x | Not supported | PHP 8.2+ required |

## When to Use Which

### Use Upstream (`renoki-co/php-k8s`) If:

- You need PHP 8.0 or 8.1 support
- You prefer string-based status values over enums
- You want the "official" version from original author
- You need proven stability without modern features

### Use This Fork (`cuppett/php-k8s`) If:

- You're using PHP 8.2+ and want modern features
- You want type-safe enums for Kubernetes states
- You need the latest Kubernetes version support
- You want comprehensive documentation and examples
- You value active maintenance and updates

## Contributing

Contributions are welcome to both projects:

- **Upstream**: https://github.com/renoki-co/php-k8s/pulls
- **This Fork**: https://github.com/cuppett/php-k8s/pulls

Where possible, improvements that benefit both projects will be contributed upstream.

## Future Direction

This fork will continue to:

1. Support latest PHP versions (8.2, 8.3, 8.4+)
2. Add modern PHP features as they become available
3. Support new Kubernetes resources and API versions
4. Maintain comprehensive documentation
5. Provide active community support

## Questions?

If you're unsure which version to use or have questions about differences:

- Open an issue: https://github.com/cuppett/php-k8s/issues
- Check the [Migration Guide](/development/migration/upstream-to-fork)
- Review the [Project History](/project/history)

## Acknowledgments

This fork exists thanks to the solid foundation provided by the upstream project. We aim to complement, not compete with, the original work.
