# Server Side Apply

Server Side Apply (SSA) API for declarative resource management with field ownership tracking.

## Overview

Server Side Apply provides declarative, idempotent resource updates with automatic conflict detection:

```php
$configmap = $cluster->configmap()
    ->setName('app-config')
    ->setLabels(['app' => 'myapp'])
    ->setData(['key1' => 'value1']);

// Basic apply
$configmap->apply('my-controller');

// Force apply (override conflicts)
$configmap->apply('my-controller', true);
```

## Method Signature

```php
public function apply(
    string $fieldManager,
    bool $force = false,
    array $query = ['pretty' => 1]
): self
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `$fieldManager` | `string` | Yes | - | Identifier for the actor applying changes. Used by Kubernetes to track field ownership. Example: `'my-controller'`, `'php-k8s-app'` |
| `$force` | `bool` | No | `false` | When `true`, takes ownership of conflicting fields from other field managers. Use with caution. |
| `$query` | `array` | No | `['pretty' => 1]` | Additional query parameters for the API request. |

## Return Value

Returns `$this` (the resource instance) after syncing with the server response, allowing for method chaining.

## Exceptions

Throws `\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException` on API errors:

| HTTP Code | Reason | Description |
|-----------|--------|-------------|
| 409 | Conflict | Another field manager owns the field being modified. Use `force=true` to take ownership or apply different fields. |
| 404 | Not Found | Resource namespace doesn't exist or resource not found. |
| 400, 422 | Validation Error | Invalid resource specification or empty field manager. |

## Field Manager

The `fieldManager` parameter identifies who owns the applied fields. Multiple managers can coexist on the same resource, each managing different fields:

```php
// Manager 1 sets labels
$cm = $cluster->configmap()
    ->setName('config')
    ->setLabels(['version' => 'v1']);
$cm->apply('manager1');

// Manager 2 sets data (no conflict)
$cm2 = $cluster->configmap()
    ->setName('config')
    ->setData(['key' => 'value']);
$cm2->apply('manager2');
```

## Force Apply

Use `force=true` to take ownership of fields managed by others:

```php
try {
    $cm->apply('new-manager');  // May conflict
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        // Take ownership
        $cm->apply('new-manager', true);
    }
}
```

## HTTP Details

- **HTTP Method**: `PATCH`
- **Content-Type**: `application/apply-patch+yaml`
- **Query Parameters**: `fieldManager` (required), `force` (optional), `pretty` (optional)

## Examples

### Create Resource

```php
$deployment = $cluster->deployment()
    ->setName('web-app')
    ->setAttribute('spec.replicas', 3);

$deployment->apply('deployment-controller');
```

### Update Resource

```php
// Idempotent - safe to repeat
$deployment->setAttribute('spec.replicas', 5);
$deployment->apply('deployment-controller');
```

### Handle Conflicts

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $cm->apply('my-manager');
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        // Conflict detected
        $cm->apply('my-manager', true);  // Force take ownership
    }
}
```

## Supported Resources

All resources implementing `InteractsWithK8sCluster`:
- ConfigMaps, Secrets
- Deployments, StatefulSets, DaemonSets
- Pods, Jobs, CronJobs
- Services, Ingresses
- PersistentVolumes, PersistentVolumeClaims
- All other standard Kubernetes resources

## See Also

- [Server Side Apply Guide](/guide/server-side-apply) - Complete documentation with examples
- [JSON Patch](/api-reference/patches/json-patch) - Surgical updates
- [JSON Merge Patch](/api-reference/patches/json-merge-patch) - Simple merge updates
- [Patching Guide](/guide/patching) - Overview of patching methods

---

*Server Side Apply API reference for cuppett/php-k8s fork*
