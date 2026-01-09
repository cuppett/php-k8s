# 集群交互

## 概述

PHP K8s 中的资源直接与 Kubernetes API 交互，允许您使用 PHP 创建、读取、更新和删除资源。核心功能围绕 `K8sResource` 类构建，该类提供了连接到 Kubernetes 集群的基本逻辑。

## 关键概念

### 资源交互

- 每个资源都扩展自基础 `K8sResource` 类
- 支持 CRUD（创建、读取、更新、删除）操作
- 可使用或不使用 YAML 文件
- 允许与现有 Kubernetes 资源交互
- 特别适用于创建自定义资源（CRD）

### 集群操作

您可以从 `KubernetesCluster` 实例执行操作，包括：

- CRUD 操作
- 导入现有 YAML 文件
- 监听资源
- 对资源执行回调

## 创建集群连接

```php
use RenokiCo\PhpK8s\KubernetesCluster;

// 直接 URL 连接
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// 从 kubeconfig 文件
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');

// 集群内配置（在 Pod 内运行时）
$cluster = KubernetesCluster::inClusterConfiguration();
```

## 资源工厂方法

PHP K8s 提供了方便的工厂方法来创建资源：

```php
use RenokiCo\PhpK8s\K8s;

// 创建 Pod 资源
$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setNamespace('default');

// 创建 Deployment
$deployment = K8s::deployment($cluster)
    ->setName('my-app')
    ->setNamespace('production');

// 创建 Service
$service = K8s::service($cluster)
    ->setName('api-service')
    ->setNamespace('default');
```

## 资源操作

### 构建资源

资源使用流畅接口构建：

```php
$configMap = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('default')
    ->setLabels(['app' => 'myapp', 'environment' => 'production'])
    ->setData([
        'DATABASE_HOST' => 'mysql.example.com',
        'DATABASE_PORT' => '3306',
        'CACHE_DRIVER' => 'redis',
    ]);
```

### 检索资源

```php
// 获取特定资源
$pod = $cluster->getPodByName('my-pod', 'default');

// 获取命名空间中的所有资源
$pods = $cluster->getAllPods('default');

// 获取所有命名空间中的所有资源
$allPods = $cluster->getAllPodsFromAllNamespaces();
```

### 修改资源

```php
// 获取资源
$deployment = $cluster->getDeploymentByName('my-app', 'production');

// 修改它
$deployment->setReplicas(5);

// 在集群中更新
$deployment->update();
```

## 资源状态

PHP K8s 跟踪资源同步状态：

```php
$pod = K8s::pod($cluster)->setName('test-pod');

// 检查资源是否已与集群同步
$pod->isSynced(); // false

// 创建资源
$pod->create();

// 现在已同步
$pod->isSynced(); // true

// 检查资源是否存在于集群中
$pod->exists(); // true
```

## 下一步

- [CRUD 操作](/zh/guide/usage/crud-operations) - 深入了解创建、读取、更新、删除
- [从 YAML 导入](/zh/guide/usage/yaml-import) - 从 YAML 文件加载资源
- [监听资源](/zh/guide/usage/watching-resources) - 实时监控资源变化
- [执行命令与日志](/zh/guide/usage/exec-logs) - 执行命令并流式传输日志

## 多集群管理

同时处理多个 Kubernetes 集群：

```php
$clusters = [
    'prod' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod'),
    'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging'),
];

foreach ($clusters as $env => $cluster) {
    $pods = $cluster->getAllPods();
    echo "{$env}: {$pods->count()} 个 Pod\n";
}
```

这种模式适用于：
- 比较不同环境的资源状态
- 同时部署到多个集群
- 从不同集群聚合指标

## Laravel 集成

如果您使用 Laravel，`laravel-php-k8s` 包提供了额外的便利：

```php
use RenokiCo\LaravelK8s\LaravelK8sFacade as K8s;

// 使用默认配置连接
$pods = K8s::getAllPods();

// 使用特定连接
$pods = K8s::connection('production')->getAllPods();
```

## 最佳实践

1. **重用集群连接** - 创建一次集群实例并重用它
2. **使用特定方法** - 为了清晰起见，优先使用 `getAllPods()` 而不是 `pod()->all()`
3. **检查资源状态** - 必要时始终检查 `isSynced()` 和 `exists()`
4. **处理错误** - 将 API 调用包装在 try-catch 块中以进行适当的错误处理
5. **使用命名空间** - 对于生产代码，始终明确指定命名空间

## 错误处理

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('nonexistent-pod');
} catch (KubernetesAPIException $e) {
    echo "API 错误: " . $e->getMessage();
    echo "状态码: " . $e->getCode();
}
```

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
