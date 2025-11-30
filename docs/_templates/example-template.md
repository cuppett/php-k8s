# Example Title

Brief description of what this example demonstrates and when you would use this pattern.

## Prerequisites

- Kubernetes cluster running
- kubectl proxy on port 8080 (or proper authentication configured)
- Any specific resources or features required

## Overview

Explain the scenario and what problem this example solves.

## Complete Example

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// Connect to cluster
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Step 1: Create initial resources
echo "Step 1: Creating resources...\n";

$resource = K8s::resourceType($cluster)
    ->setName('example-resource')
    ->setNamespace('default')
    // Configuration
    ->create();

echo "✓ Created: {$resource->getName()}\n";

// Step 2: Perform operations
echo "\nStep 2: Performing operations...\n";

// Operations here

// Step 3: Verify results
echo "\nStep 3: Verifying results...\n";

// Verification code

// Step 4: Cleanup
echo "\nStep 4: Cleaning up...\n";

$resource->delete();

echo "\n✓ Example completed successfully!\n";
```

## Step-by-Step Explanation

### Step 1: Setup and Initialization

Explain what happens in this step and why.

```php
// Relevant code snippet from above
```

### Step 2: Main Operations

Explain the core operations.

```php
// Relevant code snippet
```

### Step 3: Verification

How to verify the operations succeeded.

```php
// Verification code
```

### Step 4: Cleanup

Important cleanup steps.

```php
// Cleanup code
```

## Running the Example

1. Ensure Kubernetes cluster is running:
   ```bash
   minikube start
   kubectl proxy --port=8080 &
   ```

2. Save the example code to a file:
   ```bash
   # Save to examples/example-name.php
   ```

3. Run the example:
   ```bash
   php examples/example-name.php
   ```

## Expected Output

```
Step 1: Creating resources...
✓ Created: example-resource

Step 2: Performing operations...
...

Step 3: Verifying results...
...

Step 4: Cleaning up...

✓ Example completed successfully!
```

## Variations

### Variation 1: Alternative Approach

Show alternative ways to accomplish the same goal.

```php
// Alternative implementation
```

### Variation 2: Production Pattern

Show production-ready version with error handling.

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    // Production code with proper error handling
} catch (KubernetesAPIException $e) {
    echo "Error: {$e->getMessage()}";
}
```

## Common Issues

### Issue: Description

**Problem:** What might go wrong

**Solution:**
```php
// How to fix it
```

## Real-World Use Cases

When would you use this pattern in production?

1. **Use Case 1** - Description
2. **Use Case 2** - Description
3. **Use Case 3** - Description

## See Also

- [Related Guide](/guide/guide-name) - Detailed guide
- [Related Example](/examples/another-example) - Related pattern
- [Resource Documentation](/resources/category/resource) - Resource details

---

*Example for cuppett/php-k8s fork*
