# Authentication Issues

Troubleshooting authentication problems.

## Unauthorized Errors

**Error:** `401 Unauthorized`

**Causes:**
- Invalid token
- Expired credentials
- No authentication configured

**Solutions:**

```php
// Verify token is set
$cluster->withToken($_ENV['K8S_TOKEN']);

// Or use kubeconfig
$cluster = KubernetesCluster::fromKubeConfigYamlFile();

// Test authentication
try {
    $namespaces = $cluster->getAllNamespaces();
    echo "Authentication successful\n";
} catch (\Exception $e) {
    echo "Auth failed: {$e->getMessage()}\n";
}
```

## Forbidden Errors

**Error:** `403 Forbidden`

**Cause:** Insufficient RBAC permissions

**Solution:** Check service account permissions:

```bash
kubectl auth can-i list pods --as=system:serviceaccount:default:my-sa
```

## See Also

- [Authentication](/guide/getting-started/authentication) - Auth setup guide
- [Common Errors](/troubleshooting/common-errors) - All common errors

---

*Authentication troubleshooting for cuppett/php-k8s fork*
