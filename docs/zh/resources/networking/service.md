# Service

Services expose applications running on a set of pods.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Enums\ServiceType;

$service = K8s::service($cluster)
    ->setName('web-service')
    ->setNamespace('default')
    ->setType(ServiceType::LOAD_BALANCER)
    ->setSelectors(['app' => 'web'])
    ->setPorts([
        K8s::servicePort()
            ->setName('http')
            ->setProtocol('TCP')
            ->setPort(80)
            ->setTargetPort(8080)
    ])
    ->create();
```

## Service Types

```php
// ClusterIP (default)
$service->setType(ServiceType::CLUSTER_IP);

// NodePort
$service->setType(ServiceType::NODE_PORT);

// LoadBalancer
$service->setType(ServiceType::LOAD_BALANCER);

// ExternalName
$service->setType(ServiceType::EXTERNAL_NAME);
```

## Get Service Details

```php
$service->refresh();

if ($service->getType() === ServiceType::LOAD_BALANCER) {
    echo "LoadBalancer IP: {$service->getLoadBalancerIp()}\n";
}

echo "Cluster IP: {$service->getClusterIp()}\n";
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
