# 快速开始

本指南将带您逐步了解如何使用 PHP K8s 与您的 Kubernetes 集群进行交互的基础知识。

## 前提条件

- PHP K8s installed (see [Installation](/guide/getting-started/installation))
- Access to a Kubernetes cluster
- Basic understanding of Kubernetes concepts

## 连接到您的集群

首先，建立与您的 Kubernetes 集群的连接：

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\KubernetesCluster;

// 使用直连 URL (例如 kubectl 代理)
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// 或者使用 kubeconfig 文件
$cluster = KubernetesCluster::fromKubeConfigYamlFile('/path/to/kubeconfig.yaml');
```

## 创建 Namespace

让我们开始创建一个 Namespace：

```php
use RenokiCo\PhpK8s\K8s;

$namespace = K8s::namespace($cluster)
    ->setName('my-app')
    ->setLabels(['env' => 'development'])
    ->create();

echo "Created namespace: " . $namespace->getName() . "\n";
```

## 部署 Pod

现在部署一个简单的 nginx Pod：

```php
$pod = K8s::pod($cluster)
    ->setName('nginx-pod')
    ->setNamespace('my-app')
    ->setLabels(['app' => 'nginx'])
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
            ->setPorts([
                K8s::containerPort()->setContainerPort(80)
            ])
    ])
    ->create();

echo "Created pod: " . $pod->getName() . "\n";
echo "Pod phase: " . $pod->getPodPhase()->value . "\n";
```

## 获取 Pod 状态

等待 Pod 进入运行状态并检查其状态：

```php
// Refresh pod state from cluster
$pod->refresh();

// Check if pod is running
if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
    echo "Pod is running!\n";
    echo "Pod IP: " . $pod->getPodIp() . "\n";
}
```

## 创建 Service

通过服务暴露该 Pod：

```php
$service = K8s::service($cluster)
    ->setName('nginx-service')
    ->setNamespace('my-app')
    ->setSelectors(['app' => 'nginx'])
    ->setPorts([
        K8s::servicePort()
            ->setProtocol('TCP')
            ->setPort(80)
            ->setTargetPort(80)
    ])
    ->setType('ClusterIP')
    ->create();

echo "Created service: " . $service->getName() . "\n";
echo "Service IP: " . $service->getClusterIp() . "\n";
```

## 创建 Deployment

对于生产环境工作负载，请使用 Deployment 而非 Pod：

```php
$deployment = K8s::deployment($cluster)
    ->setName('nginx-deployment')
    ->setNamespace('my-app')
    ->setSelectors(['app' => 'nginx'])
    ->setReplicas(3)
    ->setTemplate([
        K8s::pod()
            ->setLabels(['app' => 'nginx'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()->setContainerPort(80)
                    ])
            ])
    ])
    ->create();

echo "Created deployment with " . $deployment->getReplicas() . " replicas\n";
```

## 扩展 Deployment

将该部署扩缩容至 5 个副本：

```php
$deployment->scale(5);

// 或者使用 setReplicas 和 update
$deployment->setReplicas(5)->update();

echo "Scaled deployment to " . $deployment->getReplicas() . " replicas\n";
```

## 列出资源

列出某个命名空间下的所有 Pod：

```php
$pods = $cluster->getAllPods('my-app');

foreach ($pods as $pod) {
    echo "Pod: " . $pod->getName() . " - Phase: " . $pod->getPodPhase()->value . "\n";
}
```

列出所有 namespaces:

```php
$namespaces = $cluster->getAllNamespaces();

foreach ($namespaces as $ns) {
    echo "Namespace: " . $ns->getName() . "\n";
}
```

## 获取特定资源

根据名称获取特定的Pod：

```php
$pod = $cluster->getPodByName('nginx-pod', 'my-app');

echo "Pod: " . $pod->getName() . "\n";
echo "Created: " . $pod->getCreationTimestamp() . "\n";
```

## 更新资源

为Pod更新标签：

```php
$pod->setLabels([
    'app' => 'nginx',
    'version' => 'v1.0',
    'environment' => 'production'
])->update();

echo "Updated pod labels\n";
```

## 删除资源

删除 pod:

```php
$pod->delete();

echo "Deleted pod: " . $pod->getName() . "\n";
```

删除整个 Deployment：

```php
$deployment->delete();

echo "Deleted deployment: " . $deployment->getName() . "\n";
```

## 从 YAML 导入

你也可以从 YAML 文件导入资源：

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: Pod
metadata:
  name: yaml-pod
  namespace: my-app
spec:
  containers:
  - name: nginx
    image: nginx:latest
    ports:
    - containerPort: 80
YAML;

$pod = K8s::fromYaml($cluster, $yamlContent);
$pod->create();

echo "Created pod from YAML: " . $pod->getName() . "\n";
```

## 监听资源

实时监听 pod 变更：

```php
$pod->watchAll(function ($type, $pod) {
    echo "Event: {$type} - Pod: {$pod->getName()} - Phase: {$pod->getPodPhase()->value}\n";

    if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
        return true; // 停止监听
    }
}, ['namespace' => 'my-app']);
```

## 流式日志

从容器中流式获取日志：

```php
$pod->logs(function ($line) {
    echo "Log: {$line}\n";
});
```

## 执行命令

在容器中执行命令：

```php
$output = $pod->exec(['ls', '-la', '/usr/share/nginx/html']);

foreach ($output as $line) {
    echo $line . "\n";
}
```

## 完整示例

以下是一个创建、扩缩容并监控 Deployment 的完整示例：

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// 连接到集群
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// 创建 namespace
$namespace = K8s::namespace($cluster)
    ->setName('quickstart-demo')
    ->create();

// 创建 deployment
$deployment = K8s::deployment($cluster)
    ->setName('nginx')
    ->setNamespace('quickstart-demo')
    ->setSelectors(['app' => 'nginx'])
    ->setReplicas(2)
    ->setTemplate([
        K8s::pod()
            ->setLabels(['app' => 'nginx'])
            ->setContainers([
                K8s::container()
                    ->setName('nginx')
                    ->setImage('nginx:latest')
                    ->setPorts([
                        K8s::containerPort()->setContainerPort(80)
                    ])
            ])
    ])
    ->create();

echo "✓ Created deployment: {$deployment->getName()}\n";

// 扩容
$deployment->scale(5);
echo "✓ Scaled to 5 replicas\n";

// 等待 Pods 就绪
sleep(5);

// 列出 Pods
$pods = $cluster->getAllPods('quickstart-demo');
echo "✓ Found " . count($pods) . " pods\n";

foreach ($pods as $pod) {
    echo "  - {$pod->getName()}: {$pod->getPodPhase()->value}\n";
}

// 清理
$deployment->delete();
$namespace->delete();
echo "✓ Cleaned up resources\n";
```

## 下一步

既然你已经掌握了基础知识，不妨去探索以下内容：

- [认证](/guide/getting-started/authentication) - 配置安全集群访问
- [配置](/guide/getting-started/configuration) - 高级配置选项
- [CRUD 操作](/guide/usage/crud-operations) - 深入了解资源管理
- [示例](/examples/basic-crud) - 更实际的示例

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
