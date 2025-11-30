# Resource Model

PHP K8s uses a trait-based composition model to build Kubernetes resource classes.

## Architecture Overview

```
K8sResource (base class)
    ├── Uses Traits (HasSpec, HasStatus, HasMetadata, etc.)
    ├── Implements Contracts (InteractsWithK8sCluster, Watchable, etc.)
    └── Extended by Specific Resources (K8sPod, K8sDeployment, etc.)
```

## Base Class: K8sResource

All Kubernetes resources extend `K8sResource`:

```php
abstract class K8sResource
{
    protected static string $kind;
    protected static string $defaultVersion;
    protected static bool $namespaceable = true;

    protected array $attributes = [];
    protected ?KubernetesCluster $cluster = null;
}
```

## Trait Composition

Resources compose functionality using traits:

### Core Traits

```php
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasMetadata;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;

class K8sPod extends K8sResource
{
    use HasSpec;
    use HasStatus;
    use HasMetadata;
    use HasSelector;

    protected static $kind = 'Pod';
    protected static $defaultVersion = 'v1';
}
```

### Available Traits

- **HasSpec** - Manage spec section
- **HasStatus** - Read-only status
- **HasMetadata** - Labels, annotations, name, namespace
- **HasSelector** - Label/field selectors
- **HasReplicas** - Replica management
- **HasPodTemplate** - Pod template spec
- **HasStorage** - Storage configuration

## Contracts (Interfaces)

Resources implement contracts to declare capabilities:

```php
interface InteractsWithK8sCluster
{
    public function create();
    public function update();
    public function delete();
}

interface Watchable
{
    public function watch(callable $callback);
}

interface Scalable
{
    public function scale(int $replicas);
}
```

## Resource Patterns

### Namespaced Resource

```php
class K8sPod extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'Pod';
    protected static $defaultVersion = 'v1';
    protected static $namespaceable = true;  // Namespaced
}
```

### Cluster-Scoped Resource

```php
class K8sNode extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'Node';
    protected static $defaultVersion = 'v1';
    protected static $namespaceable = false;  // Cluster-scoped
}
```

## State Management

Resources track their cluster synchronization state:

```php
$pod = K8s::pod($cluster)->setName('test');

$pod->isSynced();  // false - not yet created
$pod->exists();    // false - doesn't exist in cluster

$pod->create();

$pod->isSynced();  // true - synced with cluster
$pod->exists();    // true - exists in cluster
```

## Attribute Access

Resources provide fluent attribute access:

```php
// Set attributes
$pod->setAttribute('spec.containers.0.image', 'nginx:latest');

// Get attributes
$image = $pod->getAttribute('spec.containers.0.image');

// Use dot notation for nested paths
$pod->setAttribute('metadata.labels.app', 'myapp');
```

## Resource Lifecycle

```
1. Create instance   → new K8sPod($cluster)
2. Build spec        → setContainers(), setLabels(), etc.
3. Sync to cluster   → create()
4. Monitor state     → refresh(), isSynced()
5. Update resource   → setAttribute(), update()
6. Delete resource   → delete()
```

## Extensibility

### Custom Methods via Macros

```php
K8sPod::macro('changeDnsPolicy', function ($policy) {
    return $this->setAttribute('spec.dnsPolicy', $policy);
});

$pod->changeDnsPolicy('ClusterFirst');
```

### Custom Resource Classes

```php
class MyCustomResource extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    protected static $kind = 'MyResource';
    protected static $defaultVersion = 'mygroup.io/v1';
    protected static $namespaceable = true;

    public function customMethod()
    {
        // Custom logic
    }
}
```

## See Also

- [Trait Composition](/architecture/trait-composition) - Detailed trait documentation
- [Cluster Operations](/architecture/cluster-operations) - How resources interact with cluster
- [Extensibility](/architecture/extensibility) - Extending the resource model

---

*Architecture documentation for the resource model in cuppett/php-k8s fork*
