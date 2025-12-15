# Debugging

Techniques for debugging PHP K8s applications.

## Enable Debug Mode

```php
$cluster = new KubernetesCluster('http://127.0.0.1:8080', [
    'debug' => true,  // Enable HTTP debug output
]);
```

## Check Cluster State

```bash
# Verify cluster is accessible
kubectl get pods --all-namespaces

# Check specific resource
kubectl describe pod my-pod

# Check events
kubectl get events --sort-by='.lastTimestamp'
```

## Inspect API Responses

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('my-pod');
} catch (KubernetesAPIException $e) {
    echo "Status Code: {$e->getCode()}\n";
    echo "Message: {$e->getMessage()}\n";
    echo "Payload: " . json_encode($e->getPayload(), JSON_PRETTY_PRINT) . "\n";
}
```

## Logging

```php
// Log all operations
$operations = [
    'create' => fn() => $pod->create(),
    'update' => fn() => $pod->update(),
    'delete' => fn() => $pod->delete(),
];

foreach ($operations as $op => $fn) {
    try {
        $fn();
        echo "✓ {$op} succeeded\n";
    } catch (\Exception $e) {
        echo "✗ {$op} failed: {$e->getMessage()}\n";
    }
}
```

## Common Debug Checklist

1. ✅ Is kubectl proxy running?
2. ✅ Can kubectl access the cluster?
3. ✅ Is authentication configured?
4. ✅ Does the resource exist?
5. ✅ Are RBAC permissions correct?
6. ✅ Is the namespace correct?

## See Also

- [Common Errors](/troubleshooting/common-errors) - Common issues and solutions

---

*Debugging guide for cuppett/php-k8s fork*
