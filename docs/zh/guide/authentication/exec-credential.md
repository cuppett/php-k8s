# Exec Credential Plugin

PHP K8s supports the modern Kubernetes [exec credential plugin](https://kubernetes.io/docs/reference/access-authn-authz/authentication/#client-go-credential-plugins) format (`user.exec` in kubeconfig). This allows authentication via external commands that return temporary tokens.

## Overview

The exec credential plugin is the standard authentication method for:
- AWS EKS (`aws eks get-token`)
- Google GKE (`gcloud config config-helper`)
- Azure AKS (`kubelogin`)
- Any custom credential provider that implements the ExecCredential API

## Automatic Detection

When loading a kubeconfig file, PHP K8s automatically detects and uses exec providers:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Automatically uses exec provider if configured in kubeconfig
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');

// Tokens are automatically refreshed before expiry
$pods = $cluster->getAllPods();
```

## Kubeconfig Format

The exec credential plugin is configured in the kubeconfig `user` section:

```yaml
apiVersion: v1
kind: Config
contexts:
- context:
    cluster: my-cluster
    user: my-user
  name: my-context
clusters:
- cluster:
    server: https://api.cluster.example.com:6443
    certificate-authority-data: LS0tLS1CRUdJTi...
  name: my-cluster
users:
- name: my-user
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1
      command: aws
      args:
        - eks
        - get-token
        - --cluster-name
        - my-cluster
      env:
        - name: AWS_PROFILE
          value: my-profile
      provideClusterInfo: false
```

## Supported API Versions

- `client.authentication.k8s.io/v1` (stable)
- `client.authentication.k8s.io/v1beta1` (deprecated but supported)

## ExecCredential Response Format

The external command must output JSON in the ExecCredential format:

```json
{
  "apiVersion": "client.authentication.k8s.io/v1",
  "kind": "ExecCredential",
  "status": {
    "token": "eyJhbGciOiJSUzI1NiIsImtpZCI6...",
    "expirationTimestamp": "2025-12-09T11:00:00Z"
  }
}
```

## Environment Variables

The exec command can access:
- Environment variables from the `env` array in kubeconfig
- System environment variables
- `KUBERNETES_EXEC_INFO` (if `provideClusterInfo: true`)

### provideClusterInfo

When enabled, PHP K8s sets the `KUBERNETES_EXEC_INFO` environment variable:

```json
{
  "apiVersion": "client.authentication.k8s.io/v1",
  "kind": "ExecCredential",
  "spec": {
    "cluster": {
      "server": "https://api.cluster.example.com:6443",
      "certificate-authority-data": "LS0tLS1CRUdJTi...",
      "insecure-skip-tls-verify": false
    }
  }
}
```

## Token Refresh

PHP K8s automatically refreshes tokens:
- Before they expire (60-second buffer)
- When `isExpired()` returns true
- On the first `getToken()` call if no token exists

## Manual Usage

You can also use the exec provider directly:

```php
use RenokiCo\PhpK8s\Auth\ExecCredentialProvider;
use RenokiCo\PhpK8s\KubernetesCluster;

$provider = new ExecCredentialProvider([
    'command' => 'aws',
    'args' => [
        'eks',
        'get-token',
        '--cluster-name',
        'my-cluster',
        '--region',
        'us-east-2'
    ],
    'env' => [
        ['name' => 'AWS_PROFILE', 'value' => 'production']
    ],
    'apiVersion' => 'client.authentication.k8s.io/v1',
]);

$cluster = KubernetesCluster::fromUrl('https://my-cluster.eks.amazonaws.com')
    ->withTokenProvider($provider)
    ->withCaCertificate('/path/to/ca.crt');
```

## Error Handling

If the exec command fails, PHP K8s throws an `AuthenticationException`:

```php
try {
    $cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
    $pods = $cluster->getAllPods();
} catch (\RenokiCo\PhpK8s\Exceptions\AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
    // Check if command is installed, credentials are valid, etc.
}
```

## Install Hints

The kubeconfig can include an `installHint` field:

```yaml
user:
  exec:
    apiVersion: client.authentication.k8s.io/v1
    command: kubectl-oidc-login
    installHint: |
      kubectl-oidc-login is not installed.
      Install: https://github.com/int128/kubelogin
```

This hint is included in the exception message if the command fails.

## Examples

### AWS EKS

```yaml
user:
  exec:
    apiVersion: client.authentication.k8s.io/v1
    command: aws
    args:
      - eks
      - get-token
      - --cluster-name
      - my-cluster
    env:
      - name: AWS_PROFILE
        value: production
```

### Google GKE

```yaml
user:
  exec:
    apiVersion: client.authentication.k8s.io/v1
    command: gcloud
    args:
      - config
      - config-helper
      - --format=json
```

### Azure AKS

```yaml
user:
  exec:
    apiVersion: client.authentication.k8s.io/v1
    command: kubelogin
    args:
      - get-token
      - --login
      - azurecli
```

## Next Steps

- [AWS EKS Authentication](/guide/authentication/eks) - Native EKS authentication without AWS CLI
- [OpenShift Authentication](/guide/authentication/openshift) - OAuth and service account tokens
- [Basic Authentication](/guide/getting-started/authentication) - Other authentication methods

---

*Documentation for cuppett/php-k8s fork*
