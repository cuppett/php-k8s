# Authentication

PHP K8s supports multiple authentication methods to connect to your Kubernetes cluster.

## Kubeconfig File

The most common method is using a kubeconfig file:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Use default kubeconfig location (~/.kube/config)
$cluster = KubernetesCluster::fromKubeConfigYamlFile();

// Or specify a custom path
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');

// Or specify a context from the kubeconfig
$cluster = KubernetesCluster::fromKubeConfigYamlFile(
    '/path/to/kubeconfig.yaml',
    'my-context'
);
```

## Bearer Token

Authenticate using a bearer token:

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withToken('your-service-account-token');
```

## Client Certificates

Use client certificates for authentication:

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withCertificate('/path/to/client.crt', '/path/to/client.key');

// With CA certificate
$cluster->withCertificate('/path/to/client.crt', '/path/to/client.key', '/path/to/ca.crt');
```

## In-Cluster Configuration

When running inside a Kubernetes pod, use in-cluster configuration:

```php
$cluster = KubernetesCluster::inClusterConfiguration();
```

This automatically loads the service account token and CA certificate from the pod.

## Basic Authentication

::: warning
Basic authentication is deprecated in Kubernetes and should be avoided.
:::

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withBasicAuth('username', 'password');
```

## Kubectl Proxy

For local development, using `kubectl proxy` is the simplest option:

```bash
# Start kubectl proxy
kubectl proxy --port=8080
```

```php
// Connect without authentication
$cluster = new KubernetesCluster('http://127.0.0.1:8080');
```

## SSL Verification

### Disable SSL Verification (Development Only)

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withoutSslChecks();
```

::: danger
Never disable SSL verification in production environments.
:::

### Custom CA Certificate

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withCaCertificate('/path/to/ca.crt');
```

## Service Account Tokens

When using service accounts, you can extract the token:

```bash
# Get token from secret
kubectl get secret <secret-name> -o jsonpath='{.data.token}' | base64 --decode
```

```php
$token = 'eyJhbGciOiJSUzI1NiIsImtpZCI6Ii...';
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withToken($token);
```

## Examples

### Production Setup with Service Account

```php
$cluster = new KubernetesCluster('https://kubernetes.prod.example.com:6443');
$cluster
    ->withToken(env('K8S_TOKEN'))
    ->withCaCertificate('/etc/kubernetes/ca.crt');
```

### Development with Kubeconfig

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile(
    $_SERVER['HOME'] . '/.kube/config',
    'minikube'
);
```

### Multi-Cluster Management

```php
$clusters = [
    'production' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-cluster'),
    'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging-cluster'),
    'development' => new KubernetesCluster('http://127.0.0.1:8080'),
];

foreach ($clusters as $env => $cluster) {
    $pods = $cluster->getAllPods();
    echo "{$env}: " . count($pods) . " pods\n";
}
```

## Troubleshooting

### Permission Denied

If you get permission errors, check your RBAC configuration:

```yaml
apiVersion: v1
kind: ServiceAccount
metadata:
  name: my-app
  namespace: default
---
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRole
metadata:
  name: my-app-role
rules:
- apiGroups: ["*"]
  resources: ["*"]
  verbs: ["get", "list", "watch", "create", "update", "delete"]
---
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: my-app-binding
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: my-app-role
subjects:
- kind: ServiceAccount
  name: my-app
  namespace: default
```

### SSL Certificate Errors

If you encounter SSL certificate errors:

1. Verify the CA certificate path is correct
2. Check certificate expiration: `openssl x509 -in ca.crt -text -noout`
3. Ensure the certificate matches the cluster URL

## Advanced Authentication Methods

PHP K8s also supports cloud provider-specific and modern authentication methods:

### AWS EKS

Native token generation using AWS SDK (no CLI required):

```php
$cluster = KubernetesCluster::fromUrl($eksUrl)
    ->withEksAuth('cluster-name', 'us-east-2');
```

[Learn more about EKS authentication →](/guide/authentication/eks)

### OpenShift

Direct OAuth authentication:

```php
$cluster = KubernetesCluster::fromUrl($openshiftUrl)
    ->withOpenShiftAuth('username', 'password');
```

[Learn more about OpenShift authentication →](/guide/authentication/openshift)

### Exec Credential Plugins

Modern Kubernetes credential plugins (automatic from kubeconfig):

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
// Works with EKS, GKE, AKS, and custom providers
```

[Learn more about exec plugins →](/guide/authentication/exec-credential)

### ServiceAccount TokenRequest

Request bound service account tokens with automatic refresh:

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config')
    ->withServiceAccountToken('namespace', 'service-account', 3600);
```

[Learn more about TokenRequest →](/guide/authentication/service-account-token)

## Next Steps

- [Configuration](/guide/getting-started/configuration) - Advanced configuration options
- [CRUD Operations](/guide/usage/crud-operations) - Start managing resources
- [RBAC Examples](/examples/rbac-setup) - Set up proper access control

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
