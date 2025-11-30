# Custom Resources Examples

Examples for working with Custom Resource Definitions.

## Define CRD Class

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class IngressRoute extends K8sResource implements InteractsWithK8sCluster
{
    protected static $kind = 'IngressRoute';
    protected static $defaultVersion = 'traefik.containo.us/v1alpha1';
    protected static $namespaceable = true;
}
```

## Use CRD

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$route = new IngressRoute($cluster, [
    'metadata' => [
        'name' => 'api-route',
        'namespace' => 'default',
    ],
    'spec' => [
        'entryPoints' => ['web'],
        'routes' => [
            [
                'match' => 'Host(`api.example.com`)',
                'kind' => 'Rule',
                'services' => [
                    ['name' => 'api-service', 'port' => 8080]
                ]
            ]
        ]
    ]
]);

$route->create();

echo "IngressRoute created: {$route->getName()}\n";
```

## Using Macros

```php
use RenokiCo\PhpK8s\K8s;

K8s::registerCrd(IngressRoute::class, 'ingressRoute');

$route = K8s::ingressRoute($cluster)
    ->setName('api-route')
    ->setNamespace('default')
    ->setAttribute('spec.entryPoints', ['web'])
    ->create();
```

---

*Custom resources examples for cuppett/php-k8s fork*
