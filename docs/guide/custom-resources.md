# Custom Resources (CRDs)

PHP K8s makes it easy to work with Custom Resource Definitions (CRDs), allowing you to interact with custom Kubernetes resources as easily as built-in ones.

## Quick Start

### 1. Define Your CRD Class

```php
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class IngressRoute extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'IngressRoute';
    protected static $defaultVersion = 'traefik.containo.us/v1alpha1';
    protected static $namespaceable = true;
}
```

### 2. Use the CRD

```php
$ingressRoute = new IngressRoute($cluster, [
    'metadata' => [
        'name' => 'my-route',
        'namespace' => 'default',
    ],
    'spec' => [
        'entryPoints' => ['web'],
        'routes' => [
            [
                'match' => 'Host(`example.com`)',
                'kind' => 'Rule',
                'services' => [
                    ['name' => 'my-service', 'port' => 80]
                ]
            ]
        ]
    ]
]);

$ingressRoute->create();
```

## CRD Class Structure

### Required Properties

```php
class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The Kubernetes resource kind
     */
    protected static $kind = 'MyCRD';

    /**
     * The API version for this resource
     */
    protected static $defaultVersion = 'mygroup.example.com/v1';

    /**
     * Whether the resource is namespaced
     */
    protected static $namespaceable = true;  // or false for cluster-scoped
}
```

### Optional Traits

Add functionality by using traits:

```php
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    protected static $kind = 'MyCRD';
    protected static $defaultVersion = 'mygroup.example.com/v1';
    protected static $namespaceable = true;
}
```

## Using Macros for Dynamic CRDs

For quick prototyping or one-off CRDs, use macros:

```php
use RenokiCo\PhpK8s\K8s;

// Register a CRD macro
K8s::macro('ingressRoute', function ($cluster, array $attributes = []) {
    return new IngressRoute($cluster, $attributes);
});

// Use it like built-in resources
$route = K8s::ingressRoute($cluster)
    ->setName('my-route')
    ->setNamespace('default')
    ->setAttribute('spec.entryPoints', ['web'])
    ->create();
```

## Common CRD Examples

### Traefik IngressRoute

```php
class IngressRoute extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'IngressRoute';
    protected static $defaultVersion = 'traefik.containo.us/v1alpha1';
    protected static $namespaceable = true;
}

$route = new IngressRoute($cluster, [
    'spec' => [
        'entryPoints' => ['web', 'websecure'],
        'routes' => [
            [
                'match' => 'Host(`api.example.com`) && PathPrefix(`/v1`)',
                'kind' => 'Rule',
                'services' => [
                    ['name' => 'api-service', 'port' => 8080]
                ],
                'middlewares' => [
                    ['name' => 'api-auth']
                ]
            ]
        ],
        'tls' => [
            'secretName' => 'api-tls-cert'
        ]
    ]
]);

$route->setName('api-route')->create();
```

### Sealed Secret

```php
class SealedSecret extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'SealedSecret';
    protected static $defaultVersion = 'bitnami.com/v1alpha1';
    protected static $namespaceable = true;
}

$sealedSecret = new SealedSecret($cluster, [
    'spec' => [
        'encryptedData' => [
            'password' => 'AgBy3i4OJSWK+PiTySYZZA9rO43cGDEq...',
        ],
    ]
]);

$sealedSecret->setName('my-sealed-secret')->create();
```

### Cert-Manager Certificate

```php
class Certificate extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'Certificate';
    protected static $defaultVersion = 'cert-manager.io/v1';
    protected static $namespaceable = true;
}

$cert = new Certificate($cluster, [
    'spec' => [
        'secretName' => 'example-tls',
        'issuerRef' => [
            'name' => 'letsencrypt-prod',
            'kind' => 'ClusterIssuer',
        ],
        'dnsNames' => [
            'example.com',
            'www.example.com',
        ],
    ]
]);

$cert->setName('example-cert')->create();
```

## Import CRDs from YAML

```php
$crd = $cluster->fromYamlFile('/path/to/custom-resource.yaml');
$crd->create();
```

## Advanced CRD Features

### Watchable CRDs

Make your CRD watchable:

```php
use RenokiCo\PhpK8s\Contracts\Watchable;

class MyCRD extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    // ... class definition
}

// Watch for changes
$cluster->getResourceByName(MyCRD::class, 'my-resource')->watch(function ($type, $resource) {
    echo "{$type}: {$resource->getName()}\n";
    return false;
});
```

### Scalable CRDs

If your CRD supports scaling:

```php
use RenokiCo\PhpK8s\Contracts\Scalable;

class MyCRD extends K8sResource implements InteractsWithK8sCluster, Scalable
{
    // ... class definition

    public function scale(int $replicas)
    {
        return $this->setAttribute('spec.replicas', $replicas)->update();
    }
}
```

### CRDs with Status

```php
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class MyCRD extends K8sResource implements InteractsWithK8sCluster
{
    use HasStatus;

    // ... class definition
}

$resource = $cluster->getResourceByName(MyCRD::class, 'my-resource');
$status = $resource->getStatus();
```

## Best Practices

1. **Organize CRD classes** - Keep in dedicated directory (e.g., `app/Kubernetes/CRDs`)
2. **Use traits** - Leverage existing traits for common functionality
3. **Document your CRDs** - Add PHPDoc comments
4. **Version your CRDs** - Match Kubernetes CRD versions
5. **Test with real cluster** - Verify CRD operations work correctly
6. **Handle errors** - CRDs may have custom validation

## Error Handling

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $crd->create();
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 404) {
        echo "CRD not installed in cluster";
    } else {
        echo "Error: {$e->getMessage()}";
    }
}
```

## Complete Example

```php
<?php

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\KubernetesCluster;

// Define CRD class
class VirtualService extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'VirtualService';
    protected static $defaultVersion = 'networking.istio.io/v1beta1';
    protected static $namespaceable = true;
}

// Connect to cluster
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Create virtual service
$vs = new VirtualService($cluster, [
    'metadata' => [
        'name' => 'reviews',
        'namespace' => 'default',
    ],
    'spec' => [
        'hosts' => ['reviews'],
        'http' => [
            [
                'route' => [
                    [
                        'destination' => [
                            'host' => 'reviews',
                            'subset' => 'v1',
                        ],
                        'weight' => 90,
                    ],
                    [
                        'destination' => [
                            'host' => 'reviews',
                            'subset' => 'v2',
                        ],
                        'weight' => 10,
                    ],
                ],
            ],
        ],
    ],
]);

$vs->create();

echo "VirtualService created: {$vs->getName()}\n";

// Update traffic split
$vs->setAttribute('spec.http.0.route.0.weight', 50);
$vs->setAttribute('spec.http.0.route.1.weight', 50);
$vs->update();

echo "Traffic split updated to 50/50\n";
```

## Next Steps

- [Advanced Macros](/advanced/macros) - Deep dive into macros
- [CRD Traits](/advanced/create-classes-for-crds/helper-traits) - Available traits for CRDs
- [Examples](/examples/custom-resources) - More CRD examples

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
