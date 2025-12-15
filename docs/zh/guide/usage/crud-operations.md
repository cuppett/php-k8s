# CRUD 操作

一旦您[配置了到 Kubernetes 集群的认证](/zh/guide/getting-started/authentication)，就可以使用 CRUD 操作（创建、读取、更新、删除）。

## 获取所有资源

从命名空间获取特定类型的所有资源：

```php
// 使用资源方法
$namespaces = $cluster->namespace()->all();

// 使用便捷方法（推荐）
$namespaces = $cluster->getAllNamespaces();

// 对于命名空间资源，指定命名空间
$stagingServices = $cluster->getAllServices('staging');
$defaultPods = $cluster->getAllPods('default');
```

::: tip 结果类型
结果是一个 `RenokiCo\PhpK8s\ResourcesList` 实例，它扩展了 `\Illuminate\Support\Collection`，使您可以访问所有 Laravel 集合方法。
:::

### 集合方法

由于结果是集合，您可以使用强大的过滤和转换方法：

```php
$pods = $cluster->getAllPods('production');

// 过滤运行中的 Pod
$runningPods = $pods->filter(fn($pod) =>
    $pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING
);

// 获取 Pod 名称
$podNames = $pods->map(fn($pod) => $pod->getName());

// 按阶段统计 Pod
$podsByPhase = $pods->groupBy(fn($pod) => $pod->getPodPhase()->value);
```

## 从所有命名空间获取资源

获取所有命名空间中的资源：

```php
// 使用资源方法
$allPods = $cluster->pod()->allNamespaces();

// 使用便捷方法（推荐）
$allPods = $cluster->getAllPodsFromAllNamespaces();
$allServices = $cluster->getAllServicesFromAllNamespaces();
```

## 获取特定资源

通过名称获取单个资源：

```php
// 方法 1：使用 whereNamespace 和 whereName
$service = $cluster->service()
    ->whereNamespace('staging')
    ->whereName('nginx')
    ->get();

// 方法 2：使用 getByName（更简洁）
$service = $cluster->service()
    ->whereNamespace('staging')
    ->getByName('nginx');

// 方法 3：使用便捷方法（推荐）
$service = $cluster->getServiceByName('nginx', 'staging');

// 默认命名空间示例
$pod = $cluster->getPodByName('my-pod'); // 使用 'default' 命名空间
```

::: info 默认命名空间
默认情况下，命名空间为 `default`，可以从函数调用中省略。
:::

## 创建资源

在集群中创建新资源：

```php
// 创建命名空间
$ns = $cluster->namespace()
    ->setName('staging')
    ->setLabels(['environment' => 'staging'])
    ->create();

// 检查资源是否已同步
$ns->isSynced(); // true

// 创建 ConfigMap
$cm = K8s::configMap($cluster)
    ->setName('app-config')
    ->setNamespace('production')
    ->setData([
        'APP_NAME' => 'MyApp',
        'APP_ENV' => 'production',
    ])
    ->create();

// 创建 Pod
$pod = K8s::pod($cluster)
    ->setName('nginx-pod')
    ->setNamespace('default')
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
            ->setPorts([K8s::containerPort()->setContainerPort(80)])
    ])
    ->create();
```

### 检查资源状态

创建资源后：

```php
$pod->isSynced(); // true - 资源已与集群同步
$pod->exists(); // true - 资源存在于集群中
$pod->getName(); // 返回 Pod 名称
$pod->getNamespace(); // 返回命名空间
```

## 更新资源

使用 REPLACE 方法更新现有资源：

```php
// 获取资源
$cm = $cluster->getConfigmapByName('env', 'default');

// 修改它
$cm->addData('API_KEY', '123')
    ->addData('API_SECRET', 'xyz')
    ->update();

// 更新 Deployment 的副本数
$deployment = $cluster->getDeploymentByName('my-app');
$deployment->setReplicas(5)->update();

// 更新 Pod 标签
$pod = $cluster->getPodByName('my-pod');
$pod->setLabels([
    'app' => 'myapp',
    'version' => 'v2.0',
    'environment' => 'production'
])->update();
```

::: warning 更新方法
`update()` 方法使用 Kubernetes REPLACE 操作，它会替换整个资源。对于部分更新，请改用 [JSON Patch](/zh/guide/usage/patching)。
:::

## 删除资源

从集群中删除资源：

```php
// 简单删除
$cm = $cluster->getConfigmapByName('settings');

if ($cm->delete()) {
    echo 'ConfigMap deleted! 🎉';
}

// 删除 Pod
$pod = $cluster->getPodByName('old-pod');
$pod->delete();
```

### 删除选项

`delete()` 方法接受可选参数以进行精细控制：

```php
public function delete(
    array $query = ['pretty' => 1],
    ?int $gracePeriod = null,
    string $propagationPolicy = 'Foreground'
): bool
```

带选项的示例：

```php
// 带 30 秒宽限期删除
$pod->delete(
    query: ['pretty' => 1],
    gracePeriod: 30,
    propagationPolicy: 'Foreground'
);
```

**传播策略：**
- `Foreground` - 等待依赖项先删除
- `Background` - 立即删除，依赖项在后台删除
- `Orphan` - 留下孤立的依赖项

## 创建或更新资源

如果资源不存在则创建，存在则更新：

```php
$cluster->configmap()
    ->setName('app-config')
    ->setNamespace('default')
    ->setData(['RAND' => mt_rand(0, 999)])
    ->createOrUpdate();
```

这对于幂等操作很有用：

```php
// 第一次运行时创建，后续运行时更新
K8s::secret($cluster)
    ->setName('api-credentials')
    ->setNamespace('production')
    ->setData('api-key', base64_encode('secret-value'))
    ->createOrUpdate();
```

## 批量操作

高效处理多个资源：

```php
// 创建多个命名空间
$namespaces = ['dev', 'staging', 'production'];

foreach ($namespaces as $ns) {
    K8s::namespace($cluster)
        ->setName($ns)
        ->setLabels(['managed-by' => 'php-k8s'])
        ->createOrUpdate();
}

// 删除旧 Pod
$pods = $cluster->getAllPods('default');

$pods
    ->filter(fn($pod) => $pod->getAge() > 86400) // 超过 1 天
    ->each(fn($pod) => $pod->delete());
```

## 错误处理

始终将 CRUD 操作包装在 try-catch 块中：

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $pod = $cluster->getPodByName('my-pod', 'production');
    $pod->setReplicas(3)->update();
} catch (KubernetesAPIException $e) {
    echo "API 错误: " . $e->getMessage();
    echo "状态码: " . $e->getCode();
    echo "负载: " . json_encode($e->getPayload());
}
```

## 最佳实践

1. **使用便捷方法** - `getAllPods()` 比 `pod()->all()` 更清晰
2. **在生产中始终指定命名空间** - 不要依赖默认值
3. **使用 createOrUpdate 以实现幂等性** - 适合重复操作
4. **删除前检查存在性** - 避免不必要的 API 调用
5. **优雅处理错误** - 始终包装在 try-catch 块中
6. **使用集合方法** - 高效过滤和转换结果

## 示例

### 完整的 CRUD 工作流

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// CREATE
$cm = K8s::configMap($cluster)
    ->setName('app-settings')
    ->setNamespace('default')
    ->setData(['DEBUG' => 'true'])
    ->create();

echo "已创建: {$cm->getName()}\n";

// READ
$cm = $cluster->getConfigmapByName('app-settings');
echo "数据: " . json_encode($cm->getData()) . "\n";

// UPDATE
$cm->setData(['DEBUG' => 'false', 'LOG_LEVEL' => 'info'])->update();
echo "已更新\n";

// DELETE
$cm->delete();
echo "已删除\n";
```

## 下一步

- [从 YAML 导入](/zh/guide/usage/yaml-import) - 从 YAML 文件加载资源
- [补丁](/zh/guide/usage/patching) - 使用 JSON Patch 执行部分更新
- [监听资源](/zh/guide/usage/watching-resources) - 实时监控变化

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
