# 从 YAML 导入

PHP K8s 支持从 YAML 文件导入 Kubernetes 资源，使您可以轻松处理现有清单或与 GitOps 工作流集成。

## 前提条件

::: warning 需要 YAML 扩展
要使 YAML 导入正常工作，您需要安装 `ext-yaml` PHP 扩展：

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS（通过 PECL）
pecl install yaml

# 验证安装
php -m | grep yaml
```
:::

## 基本导入方法

PHP K8s 提供了两种主要的 YAML 导入方法：

```php
// 从 YAML 字符串导入
$resource = $cluster->fromYaml($yamlString);

// 从 YAML 文件导入
$resource = $cluster->fromYamlFile('/path/to/manifest.yaml');
```

## 单资源导入

从 YAML 导入单个资源：

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: ConfigMap
metadata:
  name: app-config
  namespace: default
data:
  DATABASE_HOST: mysql.example.com
  DATABASE_PORT: "3306"
YAML;

$configMap = $cluster->fromYaml($yamlContent);

// 资源已加载但尚未创建
$configMap->isSynced(); // false

// 在集群中创建它
$configMap->create();

echo "已创建 ConfigMap: {$configMap->getName()}";
```

## 多资源导入

当 YAML 文件包含多个资源（由 `---` 分隔）时，该方法返回一个数组：

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: Namespace
metadata:
  name: production
---
apiVersion: v1
kind: Namespace
metadata:
  name: staging
---
apiVersion: v1
kind: Namespace
metadata:
  name: development
YAML;

$namespaces = $cluster->fromYaml($yamlContent);

// $namespaces 是 K8sNamespace 实例的数组
foreach ($namespaces as $ns) {
    $ns->createOrUpdate();
    echo "{$ns->getName()} 命名空间已同步！\n";
}
```

## 从文件导入

从 YAML 文件导入资源：

```php
// 单个资源文件
$service = $cluster->fromYamlFile('/path/to/service.yaml');
$service->create();

// 多个资源文件
$resources = $cluster->fromYamlFile('/path/to/manifests.yaml');

foreach ($resources as $resource) {
    $resource->createOrUpdate();
    echo "已同步: {$resource->getName()} ({$resource->getKind()})\n";
}
```

## 模板化 YAML 导入

对于带有变量替换的动态 YAML，使用模板化导入：

### YAML 模板示例

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: "{app_name}-config"
  namespace: "{namespace}"
  labels:
    app: "{app_name}"
    environment: "{environment}"
data:
  DATABASE_HOST: "{db_host}"
  DATABASE_PORT: "{db_port}"
  CACHE_DRIVER: "{cache_driver}"
```

### PHP 实现

```php
$cm = $cluster->fromTemplatedYamlFile('/path/to/configmap-template.yaml', [
    'app_name' => 'myapp',
    'namespace' => 'production',
    'environment' => 'prod',
    'db_host' => 'mysql.prod.example.com',
    'db_port' => '3306',
    'cache_driver' => 'redis',
]);

$cm->create();
```

### 模板语法

- 使用大括号 `{}` 定义模板变量
- 变量名称应与替换数组中的键匹配
- 适用于任何 YAML 字段（metadata、spec、data 等）

```php
// 带有多个资源的模板
$resources = $cluster->fromTemplatedYamlFile('/path/to/deployment-template.yaml', [
    'app_name' => 'api-server',
    'replicas' => '3',
    'image_tag' => 'v2.1.0',
    'cpu_limit' => '500m',
    'memory_limit' => '512Mi',
]);

foreach ($resources as $resource) {
    $resource->createOrUpdate();
}
```

## 使用导入的资源

导入后，资源的行为与任何其他 PHP K8s 资源相同：

```php
$pod = $cluster->fromYamlFile('/path/to/pod.yaml');

// 创建前修改
$pod->setNamespace('custom-namespace')
    ->setLabels(['imported' => 'true', 'source' => 'yaml']);

// 在集群中创建
$pod->create();

// 检查状态
if ($pod->isSynced()) {
    echo "Pod 创建成功！";
}
```

## 高级示例

### 导入并修改

```php
$deployment = $cluster->fromYamlFile('/path/to/deployment.yaml');

// 覆盖值
$deployment
    ->setNamespace('production')
    ->setReplicas(5)
    ->setLabels(array_merge(
        $deployment->getLabels(),
        ['managed-by' => 'php-k8s']
    ))
    ->createOrUpdate();
```

### 条件创建

```php
$resources = $cluster->fromYamlFile('/path/to/manifests.yaml');

foreach ($resources as $resource) {
    // 只创建服务和部署
    if (in_array($resource->getKind(), ['Service', 'Deployment'])) {
        $resource->createOrUpdate();
        echo "已创建 {$resource->getKind()}: {$resource->getName()}\n";
    }
}
```

### 动态模板生成

```php
function deployApplication(string $environment, array $config): void
{
    global $cluster;

    $resources = $cluster->fromTemplatedYamlFile('/templates/app.yaml', [
        'environment' => $environment,
        'namespace' => $config['namespace'],
        'replicas' => $config['replicas'],
        'image' => $config['image'],
        'domain' => $config['domain'],
    ]);

    foreach ($resources as $resource) {
        $resource->createOrUpdate();
    }
}

// 部署到生产环境
deployApplication('production', [
    'namespace' => 'prod',
    'replicas' => '5',
    'image' => 'myapp:v2.0.0',
    'domain' => 'api.example.com',
]);
```

## GitOps 集成

将 YAML 导入与版本控制结合使用：

```php
// 克隆仓库
exec('git clone https://github.com/myorg/k8s-manifests /tmp/manifests');

// 应用所有清单
$files = glob('/tmp/manifests/*.yaml');

foreach ($files as $file) {
    $resources = $cluster->fromYamlFile($file);

    if (is_array($resources)) {
        foreach ($resources as $resource) {
            $resource->createOrUpdate();
        }
    } else {
        $resources->createOrUpdate();
    }

    echo "已应用: " . basename($file) . "\n";
}
```

## 错误处理

处理 YAML 解析和导入错误：

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $resource = $cluster->fromYamlFile('/path/to/manifest.yaml');
    $resource->create();
} catch (\Exception $e) {
    if ($e instanceof KubernetesAPIException) {
        echo "API 错误: " . $e->getMessage();
    } else {
        echo "YAML 错误: " . $e->getMessage();
    }
}
```

## 最佳实践

1. **验证 YAML** - 导入前确保 YAML 有效
2. **对动态配置使用模板** - 保持清单 DRY（Don't Repeat Yourself）
3. **检查资源类型** - 验证导入的资源是预期类型
4. **版本化您的清单** - 存储在版本控制中
5. **先在开发环境中测试** - 始终在开发环境中测试 YAML 导入
6. **处理数组** - 检查返回值是数组还是单个资源

## 限制

- 需要 `ext-yaml` PHP 扩展
- 模板语法很基础（没有条件或循环）
- 大型 YAML 文件可能会消耗大量内存

## 替代方案

如果您无法安装 `ext-yaml`，可以通过编程方式构建资源：

```php
// 代替 YAML 导入
$pod = K8s::pod($cluster)
    ->setName('nginx')
    ->setNamespace('default')
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
    ])
    ->create();
```

## 下一步

- [CRUD 操作](/zh/guide/usage/crud-operations) - 管理导入的资源
- [补丁](/zh/guide/usage/patching) - 使用 JSON Patch 执行部分更新
- [自定义资源](/zh/guide/usage/custom-resources) - 从 YAML 导入 CRD

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*