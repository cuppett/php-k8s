# AWS EKS Authentication

PHP K8s provides native AWS EKS authentication with automatic token refresh. You can authenticate to EKS clusters using either the AWS SDK (pure PHP) or the AWS CLI via the exec credential plugin.

## Native AWS SDK Authentication

Generate EKS tokens natively in PHP without requiring the AWS CLI:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = KubernetesCluster::fromUrl('https://YOUR_CLUSTER.eks.amazonaws.com')
    ->withEksAuth('cluster-name', 'us-east-2')
    ->withCaCertificate('/path/to/ca.crt');

// Tokens automatically refresh every 60 seconds
$pods = $cluster->getAllPods();
```

### Requirements

Install the AWS SDK for PHP:

```bash
composer require aws/aws-sdk-php
```

### AWS Credentials

The EKS token provider uses the standard AWS credential chain:

1. Environment variables (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`)
2. AWS credentials file (`~/.aws/credentials`)
3. IAM role (when running on EC2/ECS/Lambda)

### Using AWS Profiles

```php
use RenokiCo\PhpK8s\Auth\EksTokenProvider;
use RenokiCo\PhpK8s\KubernetesCluster;

$provider = new EksTokenProvider('my-cluster', 'us-east-2');
$provider->withProfile('production');

$cluster = KubernetesCluster::fromUrl($clusterUrl)
    ->withTokenProvider($provider)
    ->withCaCertificate($caCertPath);
```

### Assuming IAM Roles

For cross-account access or role assumption:

```php
$provider = new EksTokenProvider('my-cluster', 'us-east-2');
$provider->withProfile('dev-account')
         ->withAssumeRole('arn:aws:iam::123456789012:role/EKSAdminRole');

$cluster = KubernetesCluster::fromUrl($clusterUrl)
    ->withTokenProvider($provider);
```

### Getting Cluster Endpoint and CA

Use the AWS SDK to retrieve cluster information:

```php
$sdk = new \Aws\Sdk([
    'region' => 'us-east-2',
    'version' => 'latest',
    'profile' => 'production',
]);

$eksClient = $sdk->createEKS();
$clusterInfo = $eksClient->describeCluster(['name' => 'my-cluster']);

$apiUrl = $clusterInfo['cluster']['endpoint'];
$caCert = base64_decode($clusterInfo['cluster']['certificateAuthority']['data']);

// Save CA cert
file_put_contents('/tmp/ca.crt', $caCert);

// Connect
$cluster = KubernetesCluster::fromUrl($apiUrl)
    ->withEksAuth('my-cluster', 'us-east-2')
    ->withCaCertificate('/tmp/ca.crt');
```

## Exec Credential Plugin (AWS CLI)

Alternatively, use the AWS CLI via the exec credential plugin:

### Setup

1. Install AWS CLI: `brew install awscli` or `apt-get install awscli`
2. Configure AWS credentials: `aws configure`
3. Update kubeconfig: `aws eks update-kubeconfig --name my-cluster --region us-east-2`

### Usage

```php
// Automatically uses AWS CLI via exec provider
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
```

The kubeconfig will contain:

```yaml
users:
- name: arn:aws:eks:us-east-2:123456789012:cluster/my-cluster
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1
      command: aws
      args:
        - eks
        - get-token
        - --cluster-name
        - my-cluster
        - --region
        - us-east-2
```

## Token Details

### Token Format

EKS tokens follow the format:
```
k8s-aws-v1.<base64url-encoded-presigned-sts-url>
```

### Token Expiration

- Default: 60 seconds
- Tokens are pre-signed STS GetCallerIdentity requests
- Automatically refreshed before expiry

### How It Works

1. Generate a pre-signed STS `GetCallerIdentity` URL
2. Include the `x-k8s-aws-id` header with the cluster name
3. Base64url-encode the signed URL
4. Prefix with `k8s-aws-v1.`

The EKS API server validates the token by:
1. Decoding the base64url payload
2. Making the STS GetCallerIdentity request
3. Verifying the AWS signature
4. Checking RBAC permissions via aws-auth ConfigMap

## Comparison: Native SDK vs Exec Plugin

| Feature | Native SDK | Exec Plugin |
|---------|------------|-------------|
| Requires AWS CLI | No | Yes |
| Dependencies | `aws/aws-sdk-php` | `aws` command |
| Performance | Faster (no process spawn) | Slower (spawns process) |
| Credentials | AWS SDK chain | AWS CLI config |
| Token caching | In-memory | None |
| Best for | Production apps | Development/kubectl |

## Production Example

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// Environment variables for AWS credentials
// AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_SESSION_TOKEN

$cluster = KubernetesCluster::fromUrl(env('EKS_CLUSTER_URL'))
    ->withEksAuth(env('EKS_CLUSTER_NAME'), env('AWS_REGION'))
    ->withCaCertificate(env('EKS_CA_CERT_PATH'));

// Long-running daemon - tokens auto-refresh
while (true) {
    $pods = $cluster->pod()->all();
    // Process pods...
    sleep(30);
}
```

## Troubleshooting

### AuthenticationException: AWS SDK is not installed

Install the AWS SDK:

```bash
composer require aws/aws-sdk-php
```

### 401 Unauthorized

Check:
1. AWS credentials are valid: `aws sts get-caller-identity`
2. IAM principal has EKS access
3. aws-auth ConfigMap maps the IAM principal to Kubernetes RBAC
4. Cluster name and region are correct

### aws-auth ConfigMap

Ensure your IAM user/role is mapped in the aws-auth ConfigMap:

```bash
kubectl get configmap -n kube-system aws-auth -o yaml
```

Example mapping:

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: aws-auth
  namespace: kube-system
data:
  mapRoles: |
    - rolearn: arn:aws:iam::123456789012:role/EKSAdminRole
      username: admin
      groups:
        - system:masters
```

## References

- [Kubernetes Client-go Credential Plugins](https://kubernetes.io/docs/reference/access-authn-authz/authentication/#client-go-credential-plugins)
- [AWS EKS Cluster Authentication](https://docs.aws.amazon.com/eks/latest/userguide/cluster-auth.html)
- [AWS STS GetCallerIdentity](https://docs.aws.amazon.com/STS/latest/APIReference/API_GetCallerIdentity.html)

## Next Steps

- [OpenShift Authentication](/authentication/openshift) - OAuth and service account tokens
- [ServiceAccount TokenRequest](/authentication/service-account-token) - Bound tokens via API
- [Basic Authentication](/getting-started/authentication) - Other authentication methods

---

*Documentation for cuppett/php-k8s fork*
