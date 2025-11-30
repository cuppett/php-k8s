# Common Errors

Solutions to frequently encountered errors when using PHP K8s.

## Connection Errors

### "Connection refused"

**Error:**
```
cURL error 7: Failed to connect to 127.0.0.1 port 8080: Connection refused
```

**Solution:**
Ensure kubectl proxy is running:

```bash
kubectl proxy --port=8080 &
curl http://127.0.0.1:8080/version  # Test connectivity
```

### SSL Certificate Errors

**Error:**
```
cURL error 60: SSL certificate problem
```

**Solution (Development Only):**
```php
$cluster->withoutSslChecks();
```

**Solution (Production):**
```php
$cluster->withCaCertificate('/path/to/ca.crt');
```

## Authentication Errors

### "Unauthorized"

**Error:**
```
401 Unauthorized: User "system:anonymous" cannot list resource "pods"
```

**Solution:**
Provide valid authentication:

```php
// Bearer token
$cluster->withToken('your-token');

// Or kubeconfig
$cluster = KubernetesCluster::fromKubeConfigYamlFile();
```

### "Forbidden"

**Error:**
```
403 Forbidden: User lacks permission to perform action
```

**Solution:**
Check RBAC permissions:

```bash
kubectl auth can-i list pods --as=system:serviceaccount:default:my-sa
```

## Resource Errors

### "Resource not found"

**Error:**
```
404 Not Found: pods "nonexistent-pod" not found
```

**Solution:**
Verify resource exists:

```bash
kubectl get pods -n <namespace>
```

```php
try {
    $pod = $cluster->getPodByName('my-pod');
} catch (KubernetesAPIException $e) {
    if ($e->getCode() === 404) {
        echo "Pod not found";
    }
}
```

### "Already exists"

**Error:**
```
409 Conflict: pods "my-pod" already exists
```

**Solution:**
Use `createOrUpdate()` instead of `create()`:

```php
$pod->createOrUpdate();
```

## YAML Errors

### "YAML extension not installed"

**Error:**
```
Call to undefined function yaml_parse()
```

**Solution:**
Install ext-yaml:

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS
pecl install yaml

# Verify
php -m | grep yaml
```

### "YAML parse error"

**Error:**
```
YAML parse error: mapping values are not allowed in this context
```

**Solution:**
Validate your YAML:

```bash
yamllint your-file.yaml
```

## Type Errors

### "Enum comparison fails"

**Error (Migrating from upstream):**
```php
if ($pod->getPodPhase() === 'Running') {  // Always false!
```

**Solution:**
Use enum:

```php
use RenokiCo\PhpK8s\Enums\PodPhase;

if ($pod->getPodPhase() === PodPhase::RUNNING) {
    // Correct
}
```

### "Type error: string expected, enum given"

**Error:**
```
TypeError: Return value must be of type string, enum returned
```

**Solution:**
Get enum value:

```php
$phaseString = $pod->getPodPhase()->value;  // "Running"
```

## Watch Errors

### "Watch connection timeout"

**Error:**
```
Watch operation timed out
```

**Solution:**
Set timeout option:

```php
$cluster->pod()->watchAll(function ($type, $pod) {
    // ...
    return false;
}, [
    'timeoutSeconds' => 600,  // 10 minutes
]);
```

## Exec/Logs Errors

### "Container not running"

**Error:**
```
Error executing command: container not running
```

**Solution:**
Check pod phase:

```php
$pod->refresh();

if ($pod->getPodPhase() !== PodPhase::RUNNING) {
    echo "Pod not running yet: {$pod->getPodPhase()->value}";
}
```

### "No shell in container"

**Error:**
```
exec /bin/sh: no such file or directory
```

**Solution:**
Use available shell or command:

```php
// Try different shells
$output = $pod->exec(['/bin/bash', '-c', 'ls']);
// Or
$output = $pod->exec(['ls']);  // Direct command
```

## Patch Errors

### "Test operation failed"

**Error:**
```
422 Unprocessable Entity: test operation failed
```

**Solution:**
Test operation found mismatch:

```php
$patch = new JsonPatch();
$patch
    ->test('/spec/replicas', 3)  // Expected 3
    ->replace('/spec/replicas', 5);

try {
    $deployment->jsonPatch($patch);
} catch (KubernetesAPIException $e) {
    echo "Replica count has changed, refresh and retry";
    $deployment->refresh();
}
```

## Memory Errors

### "Allowed memory size exhausted"

**Error:**
```
Fatal error: Allowed memory size of X bytes exhausted
```

**Solution:**
Increase PHP memory limit:

```php
ini_set('memory_limit', '512M');
```

Or in `php.ini`:
```ini
memory_limit = 512M
```

## Namespace Errors

### "Namespace not found"

**Error:**
```
404: namespaces "production" not found
```

**Solution:**
Create namespace first:

```php
K8s::namespace($cluster)
    ->setName('production')
    ->create();
```

## Quick Debugging Checklist

1. ✅ Is kubectl proxy running?
   ```bash
   curl http://127.0.0.1:8080/version
   ```

2. ✅ Can you access the cluster with kubectl?
   ```bash
   kubectl get pods
   ```

3. ✅ Is authentication configured?
   ```php
   $cluster->withToken('...')
   ```

4. ✅ Is the namespace correct?
   ```php
   $cluster->getAllPods('correct-namespace')
   ```

5. ✅ Is ext-yaml installed (for YAML imports)?
   ```bash
   php -m | grep yaml
   ```

6. ✅ Are you using enums correctly?
   ```php
   use RenokiCo\PhpK8s\Enums\PodPhase;
   $pod->getPodPhase() === PodPhase::RUNNING
   ```

## Getting Help

If you're still stuck:

1. Check the [full documentation](https://php-k8s.cuppett.dev)
2. Search [GitHub Issues](https://github.com/cuppett/php-k8s/issues)
3. Open a new issue with:
   - PHP version (`php -v`)
   - Package version (`composer show renoki-co/php-k8s`)
   - Minimal code example
   - Full error message

---

*Troubleshooting guide for common errors in cuppett/php-k8s fork*
