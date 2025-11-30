# Resource Traits

PHP K8s uses traits to compose resource functionality. This allows resources to mix and match capabilities as needed.

## Available Traits

### HasSpec

Provides methods for managing the `spec` section of a resource.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

$deployment->setSpec('replicas', 3);
$spec = $deployment->getSpec();
```

### HasStatus

Provides read-only access to the `status` section from the cluster.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

$status = $deployment->getStatus();
```

### HasMetadata

Provides methods for labels, annotations, name, and namespace.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasMetadata;

$pod->setLabels(['app' => 'web']);
$pod->setAnnotations(['key' => 'value']);
```

### HasSelector

Provides label and field selector methods for querying.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;

$deployment->setSelectors(['app' => 'web']);
```

### HasReplicas

Provides replica management for scalable workloads.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasReplicas;

$deployment->setReplicas(5);
$count = $deployment->getReplicas();
```

### HasPodTemplate

Provides pod template management for workload resources.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasPodTemplate;

$deployment->setTemplate($podTemplate);
```

### HasStorage

Provides storage-related methods for volumes and claims.

```php
use RenokiCo\PhpK8s\Traits\Resource\HasStorage;
```

## Using Traits in Custom Resources

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\{HasSpec, HasStatus, HasMetadata};

class MyCRD extends K8sResource
{
    use HasSpec;
    use HasStatus;
    use HasMetadata;

    protected static $kind = 'MyCRD';
    protected static $defaultVersion = 'mygroup.io/v1';
}
```

## See Also

- [Resource Model](/architecture/resource-model) - Architecture overview
- [Custom Resources](/guide/custom-resources) - Creating CRDs

---

*Resource traits documentation for cuppett/php-k8s fork*
