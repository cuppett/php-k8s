# Watching Resources

The Watch API allows you to monitor Kubernetes resources in real-time, receiving notifications when resources are added, modified, or deleted.

## Overview

PHP K8s provides a PHP-native implementation of the Kubernetes Watch API, allowing you to:

- Watch specific resources by name
- Watch all resources of a type
- Filter and process resource changes
- Control watch duration and termination

## Watch a Specific Resource

Watch a single resource by name:

```php
$cluster->pod()->watchByName('mysql', function ($type, $pod) {
    echo "Event type: {$type}\n";
    echo "Pod: {$pod->getName()}\n";
    echo "Phase: {$pod->getPodPhase()->value}\n";
    echo "Resource Version: {$pod->getResourceVersion()}\n";

    // Return true to stop watching, false to continue
    return false;
});
```

### Event Types

The `$type` parameter indicates the type of change:

- `ADDED` - Resource was created
- `MODIFIED` - Resource was updated
- `DELETED` - Resource was removed

```php
use RenokiCo\PhpK8s\Enums\WatchEventType;

$cluster->pod()->watchByName('nginx', function ($type, $pod) {
    match ($type) {
        WatchEventType::ADDED->value => echo "Pod created!\n",
        WatchEventType::MODIFIED->value => echo "Pod updated!\n",
        WatchEventType::DELETED->value => echo "Pod deleted!\n",
    };

    return false; // Continue watching
});
```

## Watch All Resources

Watch all resources of a specific type:

```php
$cluster->pod()->watchAll(function ($type, $pod) {
    echo "{$type}: {$pod->getName()} - {$pod->getPodPhase()->value}\n";

    // Process specific pods
    if ($pod->getName() === 'nginx') {
        echo "Nginx pod event detected!\n";
    }

    return false; // Continue watching
}, ['namespace' => 'production']);
```

### Watch Across All Namespaces

```php
$cluster->pod()->watchAll(function ($type, $pod) {
    echo "{$type}: {$pod->getName()} in {$pod->getNamespace()}\n";
}, ['allNamespaces' => true]);
```

## Controlling the Watch

### Stop Watching

Return `true` from the callback to stop watching:

```php
$cluster->pod()->watchByName('app-pod', function ($type, $pod) {
    if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
        echo "Pod is running! Stopping watch.\n";
        return true; // Stop watching
    }

    return false; // Continue watching
});
```

### Timeout

Set a timeout for the watch operation:

```php
$cluster->pod()->watchAll(function ($type, $pod) {
    echo "{$type}: {$pod->getName()}\n";
    return false;
}, [
    'namespace' => 'default',
    'timeoutSeconds' => 300, // Watch for 5 minutes
]);
```

## Resource Version Tracking

Use resource versions to watch for changes starting from a specific point:

```php
// Get current pod
$pod = $cluster->getPodByName('mysql');
$resourceVersion = $pod->getResourceVersion();

// Watch for changes after this version
$cluster->pod()->watchByName('mysql', function ($type, $pod) {
    echo "Change detected: {$type}\n";
    return false;
}, ['resourceVersion' => $resourceVersion]);
```

This is useful for resuming watches after disconnection:

```php
$lastResourceVersion = null;

while (true) {
    try {
        $options = $lastResourceVersion
            ? ['resourceVersion' => $lastResourceVersion]
            : [];

        $cluster->pod()->watchAll(function ($type, $pod) use (&$lastResourceVersion) {
            // Process event
            echo "{$type}: {$pod->getName()}\n";

            // Track latest resource version
            $lastResourceVersion = $pod->getResourceVersion();

            return false;
        }, $options);
    } catch (\Exception $e) {
        echo "Watch failed, reconnecting...\n";
        sleep(5); // Wait before reconnecting
    }
}
```

## Filtering Events

Filter events in the callback:

```php
$cluster->deployment()->watchAll(function ($type, $deployment) {
    // Only process production deployments
    if ($deployment->getNamespace() !== 'production') {
        return false;
    }

    // Only care about ready replicas
    if ($type === 'MODIFIED') {
        $ready = $deployment->getReadyReplicas();
        $desired = $deployment->getReplicas();

        echo "{$deployment->getName()}: {$ready}/{$desired} ready\n";

        if ($ready === $desired) {
            echo "Deployment fully ready!\n";
        }
    }

    return false;
}, ['namespace' => 'production']);
```

## Advanced Examples

### Wait for Pod to be Ready

```php
function waitForPodReady(string $podName, string $namespace = 'default', int $timeout = 300): bool
{
    global $cluster;

    $startTime = time();

    return $cluster->pod()->watchByName($podName, function ($type, $pod) use ($startTime, $timeout) {
        if (time() - $startTime > $timeout) {
            echo "Timeout waiting for pod\n";
            return true; // Stop watching
        }

        if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
            echo "Pod is ready!\n";
            return true; // Stop watching
        }

        echo "Pod phase: {$pod->getPodPhase()->value}\n";
        return false; // Continue watching
    }, ['namespace' => $namespace]);
}

// Usage
if (waitForPodReady('nginx-pod')) {
    echo "Success!\n";
}
```

### Monitor Deployment Rollout

```php
$cluster->deployment()->watchByName('my-app', function ($type, $deployment) {
    if ($type !== 'MODIFIED') {
        return false;
    }

    $ready = $deployment->getReadyReplicas();
    $updated = $deployment->getUpdatedReplicas();
    $desired = $deployment->getReplicas();

    echo "Rollout: {$ready}/{$desired} ready, {$updated} updated\n";

    // Stop when all replicas are ready and updated
    if ($ready === $desired && $updated === $desired) {
        echo "Rollout complete!\n";
        return true;
    }

    return false;
}, ['namespace' => 'production']);
```

### Audit Trail

```php
use Illuminate\Support\Facades\Log;

$cluster->pod()->watchAll(function ($type, $pod) {
    Log::info('Pod event', [
        'type' => $type,
        'name' => $pod->getName(),
        'namespace' => $pod->getNamespace(),
        'phase' => $pod->getPodPhase()->value,
        'node' => $pod->getNodeName(),
        'created_at' => $pod->getCreationTimestamp(),
    ]);

    return false;
}, ['allNamespaces' => true]);
```

## Watch Options

All available watch options:

```php
$cluster->pod()->watchAll(function ($type, $pod) {
    // Process event
    return false;
}, [
    'namespace' => 'default',          // Specific namespace
    'allNamespaces' => false,          // Watch all namespaces
    'resourceVersion' => '12345',      // Start from this version
    'timeoutSeconds' => 300,           // Watch timeout
    'labelSelector' => 'app=nginx',    // Filter by labels
    'fieldSelector' => 'status.phase=Running', // Filter by fields
]);
```

### Label Selectors

```php
// Watch pods with specific labels
$cluster->pod()->watchAll(function ($type, $pod) {
    echo "{$type}: {$pod->getName()}\n";
    return false;
}, [
    'namespace' => 'production',
    'labelSelector' => 'app=nginx,environment=prod',
]);
```

### Field Selectors

```php
// Watch running pods only
$cluster->pod()->watchAll(function ($type, $pod) {
    echo "{$type}: {$pod->getName()}\n";
    return false;
}, [
    'namespace' => 'default',
    'fieldSelector' => 'status.phase=Running',
]);
```

## Error Handling

Handle watch errors gracefully:

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $cluster->pod()->watchAll(function ($type, $pod) {
        echo "{$type}: {$pod->getName()}\n";
        return false;
    });
} catch (KubernetesAPIException $e) {
    echo "Watch failed: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}
```

## Best Practices

1. **Always return boolean** - Return `true` to stop, `false` to continue
2. **Set timeouts** - Prevent infinite watches
3. **Track resource versions** - Resume watches after disconnection
4. **Filter early** - Use label/field selectors when possible
5. **Handle errors** - Wrap in try-catch for reconnection logic
6. **Limit scope** - Watch specific namespaces when possible
7. **Monitor resource usage** - Watching many resources can be memory-intensive

## Performance Considerations

- Watching all resources across all namespaces can be resource-intensive
- Use label/field selectors to reduce the amount of data processed
- Consider timeouts to prevent runaway watches
- Track resource versions for efficient reconnection

## Related Features

- **Pod Logs** - Stream logs from containers (see [Exec & Logs](/guide/usage/exec-logs))
- **Events** - Watch Event resources for cluster activity

## Next Steps

- [Exec & Logs](/guide/usage/exec-logs) - Execute commands and stream logs
- [Patching](/guide/usage/patching) - Update resources in response to changes
- [Scaling](/guide/usage/scaling) - Scale resources based on watch events

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
