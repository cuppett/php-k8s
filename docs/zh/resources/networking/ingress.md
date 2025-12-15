# Ingress

Ingress manages external access to services, typically HTTP/HTTPS.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

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

## TLS Configuration

```php
$ingress->setTls([
    [
        'hosts' => ['example.com'],
        'secretName' => 'tls-secret'
    ]
]);
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
