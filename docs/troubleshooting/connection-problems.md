# Connection Problems

Troubleshooting connection issues with Kubernetes clusters.

## Connection Refused

**Error:** `Connection refused`

**Solution:** Ensure kubectl proxy is running:

```bash
kubectl proxy --port=8080 &
curl http://127.0.0.1:8080/version
```

## SSL Errors

**Error:** `SSL certificate problem`

**Solutions:**

```php
// Development only - disable SSL verification
$cluster->withoutSslChecks();

// Production - use CA certificate
$cluster->withCaCertificate('/path/to/ca.crt');
```

## Timeout Errors

**Error:** `Operation timed out`

**Solution:** Increase timeout:

```php
$cluster->setTimeout(60);  // 60 seconds
$cluster->setConnectionTimeout(15);  // 15 seconds
```

## See Also

- [Common Errors](/troubleshooting/common-errors) - All common errors
- [Configuration](/getting-started/configuration) - Connection configuration

---

*Connection troubleshooting for cuppett/php-k8s fork*
