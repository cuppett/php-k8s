# Networking

Examples for Kubernetes networking resources.

## Create Service

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$service = K8s::service($cluster)
    ->setName('web-service')
    ->setNamespace('default')
    ->setType('LoadBalancer')
    ->setSelectors(['app' => 'web'])
    ->setPorts([
        K8s::servicePort()
            ->setName('http')
            ->setProtocol('TCP')
            ->setPort(80)
            ->setTargetPort(8080)
    ])
    ->create();

echo "Service created: {$service->getName()}\n";
```

## Create Ingress

```php
$ingress = K8s::ingress($cluster)
    ->setName('web-ingress')
    ->setNamespace('default')
    ->setRules([
        [
            'host' => 'example.com',
            'http' => [
                'paths' => [
                    [
                        'path' => '/',
                        'pathType' => 'Prefix',
                        'backend' => [
                            'service' => [
                                'name' => 'web-service',
                                'port' => ['number' => 80]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ])
    ->create();
```

---

*Networking example for cuppett/php-k8s fork*
