# Adding Resources

How to add support for new Kubernetes resources.

## Steps

### 1. Create Resource Class

```php
// src/Kinds/K8sYourResource.php
namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class K8sYourResource extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    protected static $kind = 'YourResource';
    protected static $defaultVersion = 'v1';
    protected static $namespaceable = true;
}
```

### 2. Add Factory Method

```php
// In src/Traits/InitializesResources.php
public static function yourResource($cluster = null, array $attributes = [])
{
    return new K8sYourResource($cluster, $attributes);
}
```

### 3. Create Tests

```php
// tests/YourResourceTest.php
public function test_your_resource_api_interaction()
{
    $this->runCreationTests();
    $this->runGetAllTests();
    $this->runGetTests();
    $this->runUpdateTests();
    $this->runDeletionTests();
}
```

### 4. Add Documentation

Create `docs/resources/category/your-resource.md`

## See Also

- [Development Setup](/development/setup) - Dev environment
- [Contributing](/development/contributing) - Contribution guidelines

---

*Adding resources guide for cuppett/php-k8s fork*
