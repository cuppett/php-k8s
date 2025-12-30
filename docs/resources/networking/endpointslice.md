# EndpointSlice

EndpointSlices provide a scalable way to track network endpoints.

## Basic Usage

```php
$endpointSlices = $cluster->getAllEndpointSlices('default');

foreach ($endpointSlices as $slice) {
    echo "EndpointSlice: {$slice->getName()}\n";
}
```

::: tip
EndpointSlices are typically managed by Kubernetes automatically. Manual creation is rare.
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
