# Extensibility

How to extend PHP K8s for custom needs.

## Macros

Add custom methods to existing classes:

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

K8sPod::macro('customMethod', function () {
    return $this->setAttribute('spec.customField', 'value');
});

$pod->customMethod();
```

## Custom Resource Classes

Create classes for CRDs:

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'MyCRD';
    protected static $defaultVersion = 'mygroup.io/v1';
}
```

## Custom Traits

Create reusable traits:

```php
trait HasCustomField
{
    public function setCustomField($value)
    {
        return $this->setAttribute('spec.customField', $value);
    }
}
```

## See Also

- [Custom Resources](/guide/usage/custom-resources) - CRD guide
- [Macros](/advanced/macros) - Macro usage (if exists)

---

*Extensibility documentation for cuppett/php-k8s fork*
