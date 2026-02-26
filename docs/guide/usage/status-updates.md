# Status Subresource Updates

The status subresource provides a dedicated endpoint for updating the status section of Kubernetes resources. This is essential for operators and controllers to report resource state without modifying the spec.

## Why Use Status Subresources?

In Kubernetes architecture:
- **Spec** = desired state (set by users/controllers)
- **Status** = observed state (set by controllers)

Status subresources provide:
- ✅ Separate permissions (RBAC) for status vs spec
- ✅ Optimistic concurrency for status updates
- ✅ Prevention of accidental spec changes
- ✅ Controller status reporting without spec conflicts

## Reading Status

Use the existing `getStatus()` method to read status fields:

```php
$deployment = $cluster->getDeploymentByName('my-app', 'production');

$replicas = $deployment->getStatus('replicas');
$available = $deployment->getStatus('availableReplicas');
$conditions = $deployment->getStatus('conditions', []);

// Get entire status object
$statusData = $deployment->getStatusData();
```

## Writing Status

### Setting Status Fields Locally

Before updating, set status fields on the resource:

```php
$deployment->setStatus('observedGeneration', 5);
$deployment->setStatus('replicas', 3);

// Or set the entire status object
$deployment->setStatusData([
    'replicas' => 3,
    'availableReplicas' => 2,
    'readyReplicas' => 2,
    'conditions' => [
        [
            'type' => 'Available',
            'status' => 'True',
            'lastTransitionTime' => date('c'),
        ],
    ],
]);
```

### Update Status (PUT)

Replace the entire status section:

```php
$deployment->setStatus('observedGeneration', $generation);
$deployment->updateStatus();
```

The `updateStatus()` method:
1. Calls `refreshOriginal()` and `refreshResourceVersion()`
2. Sends PUT request to `/status` endpoint
3. Syncs the response back to the resource

### JSON Patch Status (RFC 6902)

For surgical updates using JSON Patch operations:

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$patch = new JsonPatch();
$patch
    ->test('/status/replicas', 3)
    ->replace('/status/replicas', 5)
    ->add('/status/conditions/-', [
        'type' => 'Progressing',
        'status' => 'True',
        'lastTransitionTime' => date('c'),
        'reason' => 'NewReplicaSetCreated',
    ]);

$deployment->jsonPatchStatus($patch);

// Or use array format
$deployment->jsonPatchStatus([
    ['op' => 'replace', 'path' => '/status/replicas', 'value' => 5],
    ['op' => 'add', 'path' => '/status/conditions/0', 'value' => [...]],
]);
```

### JSON Merge Patch Status (RFC 7396)

For simple merging updates using JSON Merge Patch:

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

$patch = new JsonMergePatch([
    'status' => [
        'replicas' => 5,
        'availableReplicas' => 4,
    ],
]);

$deployment->jsonMergePatchStatus($patch);

// Or use array format directly
$deployment->jsonMergePatchStatus([
    'status' => [
        'replicas' => 5,
        'availableReplicas' => 4,
        'conditions' => [
            [
                'type' => 'Available',
                'status' => 'True',
                'lastTransitionTime' => date('c'),
            ],
        ],
    ],
]);
```

## Operator Pattern Examples

### Basic Controller Status Updates

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

function reconcile($cluster, $resourceName, $namespace) {
    $resource = $cluster->getConfigMapByName($resourceName, $namespace);

    // Perform reconciliation logic
    $desiredState = calculateDesiredState($resource);
    $actualState = getCurrentState($resource);

    if ($desiredState !== $actualState) {
        applyChanges($actualState, $desiredState);
    }

    // Update status to reflect observed state
    $resource->jsonMergePatchStatus([
        'status' => [
            'phase' => 'Ready',
            'observedGeneration' => $resource->getAttribute('metadata.generation'),
            'lastReconcileTime' => date('c'),
            'conditions' => [
                [
                    'type' => 'Ready',
                    'status' => 'True',
                    'lastTransitionTime' => date('c'),
                    'reason' => 'ReconciliationSucceeded',
                    'message' => 'Resource reconciled successfully',
                ],
            ],
        ],
    ]);
}
```

### Handling Controller Conflicts

Controllers actively manage status, which can cause 409 Conflict errors:

```php
try {
    $deployment->jsonMergePatchStatus([
        'status' => [
            'observedGeneration' => 5,
        ],
    ]);
} catch (\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException $e) {
    if ($e->getCode() === 409) {
        // Conflict - controller updated status simultaneously
        // Retry with fresh resource version
        $deployment = $deployment->refresh();
        $deployment->jsonMergePatchStatus([
            'status' => [
                'observedGeneration' => 5,
            ],
        ]);
    } else {
        throw $e;
    }
}
```

### Status Conditions Pattern

Kubernetes resources commonly use conditions arrays to report health:

```php
function updateCondition($resource, $type, $status, $reason, $message) {
    $conditions = $resource->getStatus('conditions', []);

    // Find existing condition of this type
    $found = false;
    foreach ($conditions as $index => $condition) {
        if ($condition['type'] === $type) {
            // Update existing condition
            $conditions[$index] = [
                'type' => $type,
                'status' => $status,
                'lastTransitionTime' => date('c'),
                'reason' => $reason,
                'message' => $message,
            ];
            $found = true;
            break;
        }
    }

    // Add new condition if not found
    if (!$found) {
        $conditions[] = [
            'type' => $type,
            'status' => $status,
            'lastTransitionTime' => date('c'),
            'reason' => $reason,
            'message' => $message,
        ];
    }

    $resource->jsonMergePatchStatus([
        'status' => [
            'conditions' => $conditions,
        ],
    ]);
}

// Usage
$configMap = $cluster->getConfigMapByName('my-app-config', 'default');
updateCondition($configMap, 'Valid', 'True', 'ValidationSucceeded', 'Configuration is valid');
updateCondition($configMap, 'Synced', 'True', 'SyncSucceeded', 'Successfully synced to target');
```

### Progressive Status Updates

Update status as work progresses:

```php
$job = $cluster->getJobByName('backup-job', 'production');

// Starting
$job->jsonMergePatchStatus([
    'status' => [
        'phase' => 'Running',
        'startTime' => date('c'),
    ],
]);

// Progress
for ($i = 1; $i <= 10; $i++) {
    performBackupStep($i);

    $job->jsonMergePatchStatus([
        'status' => [
            'progress' => "$i/10",
            'percentComplete' => $i * 10,
        ],
    ]);

    sleep(1);
}

// Completion
$job->jsonMergePatchStatus([
    'status' => [
        'phase' => 'Completed',
        'completionTime' => date('c'),
        'progress' => '10/10',
        'percentComplete' => 100,
    ],
]);
```

## Best Practices

### Never Modify Spec via Status Endpoint

The status endpoint **only** updates status - spec changes are ignored:

```php
// ❌ WRONG - spec changes ignored on /status endpoint
$deployment->setReplicas(5);  // Spec change
$deployment->updateStatus();   // Won't update replicas!

// ✅ CORRECT - use regular update for spec
$deployment->setReplicas(5)->update();

// ✅ CORRECT - separate spec and status updates
$deployment->setReplicas(5)->update();
$deployment->setStatus('observedGeneration', 5)->updateStatus();
```

### Use Optimistic Locking

Always refresh resource version before status updates:

```php
// Get fresh resource version
$resource = $resource->refresh();

// Update status
$resource->setStatus('phase', 'Ready')->updateStatus();
```

The `updateStatus()` method calls `refreshResourceVersion()` automatically, but for long-running operations, refresh beforehand.

### Prefer Merge Patch for Simple Updates

For simple field updates, use merge patch instead of full replacement:

```php
// ✅ GOOD - only updates specified fields
$deployment->jsonMergePatchStatus([
    'status' => [
        'availableReplicas' => 5,
    ],
]);

// ⚠️ LESS EFFICIENT - replaces entire status
$deployment->setStatusData([
    'replicas' => 5,
    'availableReplicas' => 5,
    'readyReplicas' => 5,
    // ... must include all fields
])->updateStatus();
```

### Handle Race Conditions

Multiple controllers can update status simultaneously:

```php
function safeUpdateStatus($resource, $statusChanges, $maxRetries = 3) {
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $resource->jsonMergePatchStatus([
                'status' => $statusChanges,
            ]);
            return true;
        } catch (\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException $e) {
            if ($e->getCode() === 409 && $attempt < $maxRetries) {
                // Conflict - refresh and retry
                $resource = $resource->refresh();
                continue;
            }
            throw $e;
        }
    }
    return false;
}
```

### Status Path Verification

Verify the correct path is being used:

```php
$deployment = $cluster->deployment()
    ->setName('my-app')
    ->setNamespace('production');

echo $deployment->resourceStatusPath();
// Output: /apis/apps/v1/namespaces/production/deployments/my-app/status
```

## Common Use Cases

### Custom Resource Status

```php
// Update custom resource status
$customResource = $cluster->customResource()
    ->setGroup('example.com')
    ->setVersion('v1')
    ->setKind('Database')
    ->setName('my-database')
    ->setNamespace('default');

$customResource->jsonMergePatchStatus([
    'status' => [
        'connected' => true,
        'endpoint' => 'postgresql://...',
        'version' => '14.5',
    ],
]);
```

### Reporting Errors

```php
try {
    performOperation();
    $resource->jsonMergePatchStatus([
        'status' => [
            'phase' => 'Succeeded',
            'lastError' => null,
        ],
    ]);
} catch (\Exception $e) {
    $resource->jsonMergePatchStatus([
        'status' => [
            'phase' => 'Failed',
            'lastError' => $e->getMessage(),
        ],
    ]);
}
```

---

*Documentation for cuppett/php-k8s fork*
