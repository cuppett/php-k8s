# OpenShift Authentication

PHP K8s provides native OpenShift authentication supporting both OAuth flows and service account tokens with automatic refresh.

## OAuth Authentication

Authenticate to OpenShift using username and password via the OAuth flow:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = KubernetesCluster::fromUrl('https://api.openshift.example.com:6443')
    ->withOpenShiftAuth('username', 'password');

// Tokens automatically refresh before expiry
$projects = $cluster->getAllNamespaces();
```

## How OAuth Works

The OpenShift OAuth provider:

1. **Discovers OAuth endpoint** from `/.well-known/oauth-authorization-server`
2. **Requests token** from `/oauth/authorize?response_type=token&client_id=openshift-challenging-client`
3. **Authenticates** using HTTP Basic auth with username/password
4. **Parses token** from the 302 redirect Location header fragment
5. **Tracks expiration** and auto-refreshes before expiry

### OAuth Endpoint Discovery

PHP K8s automatically discovers the OAuth server endpoint. For standard OpenShift:

```
API URL:    https://api.cluster.example.com:6443
OAuth URL:  https://oauth-openshift.apps.cluster.example.com
```

For OpenShift ROSA:

```
API URL:    https://api.rosa.example.p3.openshiftapps.com:443
OAuth URL:  https://oauth.rosa.example.p3.openshiftapps.com:443
```

### Manual OAuth Endpoint

Override the auto-discovered endpoint:

```php
use RenokiCo\PhpK8s\Auth\OpenShiftOAuthProvider;

$provider = new OpenShiftOAuthProvider(
    'https://api.openshift.example.com:6443',
    'username',
    'password'
);
$provider->withOAuthEndpoint('https://custom-oauth.example.com');

$cluster = KubernetesCluster::fromUrl('https://api.openshift.example.com:6443')
    ->withTokenProvider($provider);
```

## ServiceAccount Tokens (Legacy)

For OpenShift 4.10 and earlier, service account tokens are auto-generated:

```bash
# Get service account token
oc sa get-token my-service-account -n my-namespace
```

```php
$cluster = new KubernetesCluster('https://api.openshift.example.com:6443');
$cluster->withToken('eyJhbGciOiJSUzI1NiIsImtpZCI6...');
```

## ServiceAccount Tokens (Modern - 4.11+)

Starting with OpenShift 4.11, use the TokenRequest API for bound tokens. See [ServiceAccount TokenRequest](/guide/authentication/service-account-token).

## SSL Verification

For development or self-signed certificates:

```php
$cluster = KubernetesCluster::fromUrl('https://api.openshift.local:6443')
    ->withOpenShiftAuth('admin', 'password')
    ->withoutSslChecks();
```

::: danger
Never disable SSL verification in production.
:::

## Token Expiration

OpenShift OAuth tokens typically expire after 24 hours. PHP K8s:
- Tracks the `expires_in` parameter from the OAuth response
- Automatically refreshes tokens 60 seconds before expiry
- Handles refresh failures gracefully

## OpenShift-Specific Features

### Projects (Namespaces)

OpenShift projects are Kubernetes namespaces:

```php
// List all projects
$projects = $cluster->getAllNamespaces();

foreach ($projects as $project) {
    echo $project->getName() . "\n";
}
```

### Routes

OpenShift Routes require a custom resource definition. See [Custom Resources (CRDs)](/guide/usage/custom-resources).

## Production Example

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Using environment variables for credentials
$cluster = KubernetesCluster::fromUrl(env('OPENSHIFT_API_URL'))
    ->withOpenShiftAuth(
        env('OPENSHIFT_USERNAME'),
        env('OPENSHIFT_PASSWORD')
    );

// Monitor deployments
$deployments = $cluster->deployment()
    ->whereNamespace('production')
    ->all();

foreach ($deployments as $deploy) {
    echo "{$deploy->getName()}: {$deploy->getReplicas()} replicas\n";
}
```

## Exec Credential Plugin

For `oc` CLI integration, use the exec credential plugin:

```bash
oc login https://api.openshift.example.com:6443 --username=user
```

This creates a kubeconfig with:

```yaml
users:
- name: user/api-openshift-example-com:6443
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1
      command: oc
      args:
        - whoami
        - --show-token
```

Then use with PHP K8s:

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
```

## OpenShift ROSA Example

Red Hat OpenShift Service on AWS (ROSA):

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// ROSA cluster URLs follow this pattern
$apiUrl = 'https://api.rosa-cluster-name.xxxx.p3.openshiftapps.com:443';

$cluster = KubernetesCluster::fromUrl($apiUrl)
    ->withOpenShiftAuth('your-username', 'your-password');

// Access cluster resources
$namespaces = $cluster->getAllNamespaces();
$pods = $cluster->pod()->all();
```

## Troubleshooting

### AuthenticationException: OpenShift OAuth failed: expected 302

**Cause**: Username or password incorrect, or OAuth server unreachable

**Solution**:
1. Verify credentials: `oc login --username=user --password=pass`
2. Check OAuth endpoint is accessible
3. Verify API URL is correct

### AuthenticationException: no access_token in redirect

**Cause**: OAuth flow didn't return a token

**Solution**:
1. Check if the identity provider is configured correctly
2. Verify username/password authentication is enabled
3. Try manual OAuth flow to debug

### 403 Forbidden after successful authentication

**Cause**: User lacks RBAC permissions

**Solution**: Grant appropriate roles to the user:

```bash
oc adm policy add-cluster-role-to-user cluster-admin username
```

## Identity Providers

OpenShift supports multiple identity providers:
- HTPasswd
- LDAP
- GitHub/GitLab OAuth
- OpenID Connect
- Request Header

The `withOpenShiftAuth()` method works with HTPasswd and LDAP providers.

## References

- [OpenShift Authentication](https://docs.openshift.com/container-platform/latest/authentication/index.html)
- [OpenShift OAuth](https://docs.openshift.com/container-platform/latest/authentication/configuring-oauth-clients.html)
- [ROSA Documentation](https://docs.aws.amazon.com/rosa/latest/userguide/what-is-rosa.html)

## Next Steps

- [Exec Credential Plugin](/guide/authentication/exec-credential) - Standard Kubernetes exec auth
- [ServiceAccount TokenRequest](/guide/authentication/service-account-token) - Bound service account tokens
- [AWS EKS Authentication](/guide/authentication/eks) - Native EKS authentication

---

*Documentation for cuppett/php-k8s fork*
