# ServiceAccount TokenRequest

PHP K8s supports the Kubernetes [TokenRequest API](https://kubernetes.io/docs/reference/kubernetes-api/authentication-resources/token-request-v1/) for requesting bound, time-limited service account tokens from external applications.

## Overview

Starting with Kubernetes 1.22 and OpenShift 4.11, service account tokens are no longer automatically created as secrets. Instead, use the TokenRequest API to obtain bound tokens with:

- **Configurable expiration** (expirationSeconds)
- **Audience restrictions** for security
- **Automatic refresh** before expiry

## Basic Usage

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Bootstrap with your admin credentials
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config')
    ->withServiceAccountToken('my-namespace', 'my-service-account', 3600);

// Now using the service account token with automatic refresh
$pods = $cluster->pod()->whereNamespace('my-namespace')->all();
```

## Bootstrap Pattern

The TokenRequest API requires existing cluster authentication to make the API call. This is called the "bootstrap" pattern:

```php
use RenokiCo\PhpK8s\Auth\ServiceAccountTokenProvider;
use RenokiCo\PhpK8s\KubernetesCluster;

// Step 1: Connect with admin credentials
$bootstrap = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');

// Step 2: Create token provider for service account
$saProvider = new ServiceAccountTokenProvider(
    $bootstrap,
    'production',
    'app-service-account'
);
$saProvider->withExpirationSeconds(3600)  // 1 hour
           ->withAudiences(['https://kubernetes.default.svc']);

// Step 3: Create new cluster connection using the SA token
$cluster = KubernetesCluster::fromUrl($bootstrap->getUrl())
    ->withTokenProvider($saProvider);

// The cluster now uses the service account token
$deployments = $cluster->deployment()->all();
```

## Configuration

### Expiration Time

```php
// 1 hour (default)
$cluster->withServiceAccountToken('default', 'my-sa', 3600);

// 4 hours
$cluster->withServiceAccountToken('default', 'my-sa', 14400);

// 24 hours (maximum depends on cluster configuration)
$cluster->withServiceAccountToken('default', 'my-sa', 86400);
```

### Audience Restrictions

```php
$provider = new ServiceAccountTokenProvider($bootstrap, 'default', 'my-sa');
$provider->withAudiences([
    'https://kubernetes.default.svc',
    'https://my-external-service.example.com'
]);

$cluster = KubernetesCluster::fromUrl($apiUrl)
    ->withTokenProvider($provider);
```

## TokenRequest API Details

### API Endpoint

```
POST /api/v1/namespaces/{namespace}/serviceaccounts/{name}/token
```

### Request Body

```json
{
  "apiVersion": "authentication.k8s.io/v1",
  "kind": "TokenRequest",
  "spec": {
    "audiences": ["https://kubernetes.default.svc"],
    "expirationSeconds": 3600
  }
}
```

### Response Format

```json
{
  "apiVersion": "authentication.k8s.io/v1",
  "kind": "TokenRequest",
  "status": {
    "token": "eyJhbGciOiJSUzI1NiIsImtpZCI6...",
    "expirationTimestamp": "2025-12-09T12:00:00Z"
  }
}
```

## Automatic Token Refresh

PHP K8s automatically refreshes service account tokens:

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config')
    ->withServiceAccountToken('default', 'worker-sa', 1800); // 30 minutes

// Long-running process
while (true) {
    // Token automatically refreshes 60 seconds before expiry
    $jobs = $cluster->job()->all();
    processJobs($jobs);
    sleep(60);
}
```

The refresh buffer (default 60 seconds) can be configured:

```php
$provider = new ServiceAccountTokenProvider($bootstrap, 'default', 'my-sa');
$provider->setRefreshBuffer(120); // Refresh 2 minutes before expiry
```

## RBAC Permissions

The service account needs appropriate RBAC permissions:

```yaml
apiVersion: v1
kind: ServiceAccount
metadata:
  name: app-service-account
  namespace: production
---
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  name: app-role
  namespace: production
rules:
- apiGroups: [""]
  resources: ["pods", "services"]
  verbs: ["get", "list", "watch"]
- apiGroups: ["apps"]
  resources: ["deployments"]
  verbs: ["get", "list", "watch", "update", "patch"]
---
apiVersion: rbac.authorization.k8s.io/v1
kind: RoleBinding
metadata:
  name: app-rolebinding
  namespace: production
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: Role
  name: app-role
subjects:
- kind: ServiceAccount
  name: app-service-account
  namespace: production
```

## Use Cases

### External Monitoring Service

```php
// Bootstrap with admin kubeconfig
$admin = KubernetesCluster::fromKubeConfigYamlFile('/etc/k8s/admin.yaml');

// Switch to monitoring service account
$cluster = $admin->withServiceAccountToken('monitoring', 'prometheus-sa', 3600);

// Collect metrics
$pods = $cluster->pod()->all();
foreach ($pods as $pod) {
    collectMetrics($pod);
}
```

### CI/CD Pipeline

```php
// CI system has credentials to request tokens
$bootstrap = KubernetesCluster::fromUrl(env('K8S_API_URL'))
    ->withToken(env('CI_BOOTSTRAP_TOKEN'))
    ->withCaCertificate(env('K8S_CA_CERT'));

// Get deployment service account token
$cluster = $bootstrap->withServiceAccountToken('production', 'deployer-sa', 1800);

// Deploy application
$deployment = $cluster->deployment()
    ->whereName('my-app')
    ->get();
$deployment->setImage('containers.image', 'myapp:v2.0')
          ->update();
```

### OpenShift 4.11+ with Service Accounts

```php
// Authenticate with user OAuth
$userCluster = KubernetesCluster::fromUrl($apiUrl)
    ->withOpenShiftAuth('admin-user', 'password');

// Switch to service account for app operations
$appCluster = $userCluster->withServiceAccountToken(
    'my-project',
    'app-service-account',
    7200  // 2 hours
);

// Deploy with service account
$pods = $appCluster->pod()->all();
```

## In-Cluster Configuration

When running inside a pod, use the mounted service account token:

```php
// This uses the pod's service account automatically
$cluster = KubernetesCluster::inClusterConfiguration();
```

This loads the token from `/var/run/secrets/kubernetes.io/serviceaccount/token` which is already a bound token with automatic refresh handled by the kubelet.

## Comparison: Static vs Bound Tokens

| Feature | Static Token | Bound Token (TokenRequest) |
|---------|--------------|----------------------------|
| Expiration | Never (legacy) | Configurable |
| Auto-refresh | No | Yes |
| Audience | Any | Restricted |
| Security | Lower | Higher |
| Kubernetes version | Any | 1.22+ |
| OpenShift version | 4.10 and earlier | 4.11+ |
| Creation | Auto-generated secret | API request |

## Troubleshooting

### AuthenticationException: Failed to request service account token

**Causes**:
1. Bootstrap credentials invalid
2. Service account doesn't exist
3. No permission to create tokens for the service account

**Solution**:

```bash
# Check service account exists
kubectl get sa my-service-account -n my-namespace

# Verify you can request tokens manually
kubectl create token my-service-account -n my-namespace --duration=1h
```

### RBAC Permission Required

The bootstrap user/service account needs permission to create tokens:

```yaml
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  name: token-requester
  namespace: my-namespace
rules:
- apiGroups: [""]
  resources: ["serviceaccounts/token"]
  resourceNames: ["my-service-account"]
  verbs: ["create"]
```

## References

- [Kubernetes TokenRequest API](https://kubernetes.io/docs/reference/kubernetes-api/authentication-resources/token-request-v1/)
- [Bound Service Account Tokens](https://kubernetes.io/docs/reference/access-authn-authz/service-accounts-admin/#bound-service-account-token-volume)
- [OpenShift Service Accounts](https://docs.openshift.com/container-platform/latest/authentication/using-service-accounts-in-applications.html)

## Next Steps

- [OpenShift OAuth](/guide/authentication/openshift) - User authentication for OpenShift
- [AWS EKS Authentication](/guide/authentication/eks) - EKS-specific authentication
- [RBAC Examples](/examples/rbac-setup) - Configure service account permissions

---

*Documentation for cuppett/php-k8s fork*
