# 认证

PHP K8s 支持多种认证方法来连接到您的 Kubernetes 集群。

## Kubeconfig 文件

最常见的方法是使用 kubeconfig 文件：

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// 使用默认 kubeconfig 位置 (~/.kube/config)
$cluster = KubernetesCluster::fromKubeConfigYamlFile();

// 或指定自定义路径
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');

// 或从 kubeconfig 中指定上下文
$cluster = KubernetesCluster::fromKubeConfigYamlFile(
    '/path/to/kubeconfig.yaml',
    'my-context'
);
```

## Bearer Token

使用 bearer token 进行认证：

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withToken('your-service-account-token');
```

## 客户端证书

使用客户端证书进行认证：

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withCertificate('/path/to/client.crt', '/path/to/client.key');

// 带 CA 证书
$cluster->withCertificate('/path/to/client.crt', '/path/to/client.key', '/path/to/ca.crt');
```

## 集群内配置

在 Kubernetes Pod 内部运行时，使用集群内配置：

```php
$cluster = KubernetesCluster::inClusterConfiguration();
```

这会自动从 Pod 加载服务账户令牌和 CA 证书。

## 基本认证

::: warning
基本认证在 Kubernetes 中已被弃用，应避免使用。
:::

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withBasicAuth('username', 'password');
```

## Kubectl Proxy

对于本地开发，使用 `kubectl proxy` 是最简单的选择：

```bash
# 启动 kubectl proxy
kubectl proxy --port=8080
```

```php
// 无需认证连接
$cluster = new KubernetesCluster('http://127.0.0.1:8080');
```

## SSL 验证

### 禁用 SSL 验证（仅开发环境）

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withoutSslChecks();
```

::: danger
切勿在生产环境中禁用 SSL 验证。
:::

### 自定义 CA 证书

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withCaCertificate('/path/to/ca.crt');
```

## 服务账户令牌

使用服务账户时，您可以提取令牌：

```bash
# 从 secret 获取令牌
kubectl get secret <secret-name> -o jsonpath='{.data.token}' | base64 --decode
```

```php
$token = 'eyJhbGciOiJSUzI1NiIsImtpZCI6Ii...';
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
$cluster->withToken($token);
```

## 示例

### 使用服务账户的生产环境设置

```php
$cluster = new KubernetesCluster('https://kubernetes.prod.example.com:6443');
$cluster
    ->withToken(env('K8S_TOKEN'))
    ->withCaCertificate('/etc/kubernetes/ca.crt');
```

### 使用 Kubeconfig 的开发环境

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile(
    $_SERVER['HOME'] . '/.kube/config',
    'minikube'
);
```

### 多集群管理

```php
$clusters = [
    'production' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-cluster'),
    'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging-cluster'),
    'development' => new KubernetesCluster('http://127.0.0.1:8080'),
];

foreach ($clusters as $env => $cluster) {
    $pods = $cluster->getAllPods();
    echo "{$env}: " . count($pods) . " 个 Pod\n";
}
```

## 故障排除

### 权限被拒绝

如果您遇到权限错误，请检查您的 RBAC 配置：

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

### SSL 证书错误

如果您遇到 SSL 证书错误：

1. 验证 CA 证书路径是否正确
2. 检查证书过期时间：`openssl x509 -in ca.crt -text -noout`
3. 确保证书与集群 URL 匹配

## 高级认证方法

PHP K8s 还支持云提供商特定的和现代的认证方法：

### AWS EKS

使用 AWS SDK 本地生成令牌（无需 CLI）：

```php
$cluster = KubernetesCluster::fromUrl($eksUrl)
    ->withEksAuth('cluster-name', 'us-east-2');
```

[了解更多关于 EKS 认证的信息 →](/zh/guide/authentication/eks)

### OpenShift

直接 OAuth 认证：

```php
$cluster = KubernetesCluster::fromUrl($openshiftUrl)
    ->withOpenShiftAuth('username', 'password');
```

[了解更多关于 OpenShift 认证的信息 →](/zh/guide/authentication/openshift)

### Exec 凭证插件

现代 Kubernetes 凭证插件（从 kubeconfig 自动识别）：

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
// 适用于 EKS、GKE、AKS 和自定义提供商
```

[了解更多关于 exec 插件的信息 →](/zh/guide/authentication/exec-credential)

### ServiceAccount TokenRequest

请求带自动刷新的绑定服务账户令牌：

```php
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config')
    ->withServiceAccountToken('namespace', 'service-account', 3600);
```

[了解更多关于 TokenRequest 的信息 →](/zh/guide/authentication/service-account-token)

## 下一步

- [配置](/zh/guide/getting-started/configuration) - 高级配置选项
- [CRUD 操作](/zh/guide/usage/crud-operations) - 开始管理资源
- [RBAC 示例](/zh/examples/rbac-setup) - 设置适当的访问控制

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
