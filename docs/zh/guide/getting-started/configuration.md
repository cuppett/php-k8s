# 配置

本指南涵盖了 PHP K8s 的高级配置选项。

## 集群配置

### 基本设置

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
```

### HTTP 客户端选项

配置 Guzzle HTTP 客户端选项：

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443', [
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => true,
    'http_errors' => true,
]);
```

### 自定义头信息

向所有请求添加自定义头信息：

```php
$cluster->setHeaders([
    'X-Custom-Header' => 'value',
    'User-Agent' => 'MyApp/1.0',
]);
```

## SSL/TLS 配置

### 自定义 CA 证书

```php
$cluster->withCaCertificate('/path/to/ca.crt');
```

### 客户端证书

```php
$cluster->withCertificate(
    '/path/to/client.crt',
    '/path/to/client.key',
    '/path/to/ca.crt'
);
```

### 禁用 SSL 验证

::: danger 仅开发环境
切勿在生产环境中禁用 SSL 验证！
:::

```php
$cluster->withoutSslChecks();
```

## 超时配置

配置不同的超时值：

```php
// 连接超时（默认：10 秒）
$cluster->setConnectionTimeout(15);

// 请求超时（默认：30 秒）
$cluster->setTimeout(60);

// 对于长时间运行的操作
$cluster->setTimeout(300); // 5 分钟
```

## 命名空间配置

为所有操作设置默认命名空间：

```php
$cluster->setDefaultNamespace('my-app');

// 所有操作将默认使用 'my-app' 命名空间
$pods = $cluster->getAllPods(); // 从 'my-app' 获取 Pods
```

## Laravel 配置

使用 Laravel 包时，发布并编辑 `config/k8s.php`：

```php
return [
    /*
    |--------------------------------------------------------------------------
    | 默认集群连接
    |--------------------------------------------------------------------------
    */
    'default' => env('K8S_CLUSTER', 'default'),

    /*
    |--------------------------------------------------------------------------
    | 集群连接
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'default' => [
            'url' => env('K8S_URL', 'http://127.0.0.1:8080'),
            'auth' => [
                'type' => env('K8S_AUTH_TYPE', 'kubeconfig'),
                'kubeconfig' => env('K8S_KUBECONFIG', null),
                'context' => env('K8S_CONTEXT', null),
                'token' => env('K8S_TOKEN', null),
            ],
            'ssl' => [
                'verify' => env('K8S_SSL_VERIFY', true),
                'ca' => env('K8S_SSL_CA', null),
                'cert' => env('K8S_SSL_CERT', null),
                'key' => env('K8S_SSL_KEY', null),
            ],
            'timeout' => env('K8S_TIMEOUT', 30),
            'connect_timeout' => env('K8S_CONNECT_TIMEOUT', 10),
        ],

        'production' => [
            'url' => env('K8S_PROD_URL'),
            'auth' => [
                'type' => 'token',
                'token' => env('K8S_PROD_TOKEN'),
            ],
            'ssl' => [
                'verify' => true,
                'ca' => env('K8S_PROD_CA'),
            ],
        ],
    ],
];
```

然后在您的 Laravel 应用程序中使用：

```php
use RenokiCo\LaravelK8s\LaravelK8sFacade as K8s;

// 使用默认连接
$pods = K8s::getAllPods();

// 使用特定连接
$pods = K8s::connection('production')->getAllPods();
```

## 环境变量

用于配置的常见环境变量：

```env
# 集群 URL
K8S_URL=https://kubernetes.example.com:6443

# 认证
K8S_AUTH_TYPE=token
K8S_TOKEN=your-service-account-token

# 或使用 kubeconfig
K8S_AUTH_TYPE=kubeconfig
K8S_KUBECONFIG=/path/to/kubeconfig.yaml
K8S_CONTEXT=my-context

# SSL
K8S_SSL_VERIFY=true
K8S_SSL_CA=/path/to/ca.crt
K8S_SSL_CERT=/path/to/client.crt
K8S_SSL_KEY=/path/to/client.key

# 超时
K8S_TIMEOUT=30
K8S_CONNECT_TIMEOUT=10

# 默认命名空间
K8S_NAMESPACE=default
```

## 日志记录

启用请求/响应日志记录以进行调试：

```php
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// 添加日志中间件
$cluster->setHttpClientOptions([
    'handler' => $stack,
    'debug' => true, // 启用调试模式
]);
```

对于 Laravel，使用内置日志记录：

```php
use Illuminate\Support\Facades\Log;

// 日志将自动包含 K8s 操作
Log::channel('k8s')->info('Creating pod', ['name' => 'my-pod']);
```

## 重试配置

配置失败请求的自动重试：

```php
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// 最多重试 3 次，带有指数退避
$cluster->setHttpClientOptions([
    'retry_on_status' => [429, 503],
    'retry_count' => 3,
    'retry_delay' => 1000, // 毫秒
]);
```

## 速率限制

实现速率限制以避免压垮 API 服务器：

```php
use RenokiCo\PhpK8s\RateLimiter;

$rateLimiter = new RateLimiter(
    maxRequests: 100,
    perSeconds: 60
);

$cluster->setRateLimiter($rateLimiter);
```

## 资源默认值

为资源设置默认值：

```php
K8s::pod()->setDefaults([
    'imagePullPolicy' => 'Always',
    'restartPolicy' => 'Always',
]);

// 所有 Pod 将具有这些默认值
$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setContainers([...])
    ->create(); // imagePullPolicy 和 restartPolicy 会自动设置
```

## 多集群配置

高效管理多个集群：

```php
class ClusterManager
{
    private array $clusters = [];

    public function __construct()
    {
        $this->clusters = [
            'prod-us' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-us-east-1'),
            'prod-eu' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-eu-west-1'),
            'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging'),
            'dev' => new KubernetesCluster('http://127.0.0.1:8080'),
        ];
    }

    public function get(string $name): KubernetesCluster
    {
        return $this->clusters[$name] ?? throw new \InvalidArgumentException("Cluster {$name} not found");
    }

    public function all(): array
    {
        return $this->clusters;
    }
}

// 使用
$manager = new ClusterManager();
$prodPods = $manager->get('prod-us')->getAllPods();
```

## 性能调优

### 连接池

重用连接以获得更好的性能：

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443', [
    'http_version' => '2.0', // 如果支持，使用 HTTP/2
]);
```

### 批量操作

尽可能批量操作：

```php
// 而不是逐个创建 Pod
$pods = [];
for ($i = 0; $i < 10; $i++) {
    $pods[] = K8s::pod($cluster)
        ->setName("pod-{$i}")
        ->setContainers([...])
        ->toArray();
}

// 使用 kubectl apply 一次性创建所有
K8s::fromYaml($cluster, yaml_emit($pods))->create();
```

### 缓存

为频繁访问的资源实现缓存：

```php
use Illuminate\Support\Facades\Cache;

$namespaces = Cache::remember('k8s.namespaces', 300, function () use ($cluster) {
    return $cluster->getAllNamespaces();
});
```

## 下一步

- [CRUD 操作](/zh/guide/usage/crud-operations) - 开始管理资源
- [认证](/zh/guide/getting-started/authentication) - 配置安全访问
- [示例](/zh/examples/basic-crud) - 查看配置实际应用

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
