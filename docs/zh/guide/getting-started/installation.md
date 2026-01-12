# 安装

## 要求

在安装 PHP K8s 之前，请确保您的环境满足以下要求：

- **PHP**: 8.2 或更高版本（推荐 8.3+）
- **扩展**:
  - `ext-json`（必需）
  - `ext-yaml`（可选，但推荐用于 YAML 支持）
- **依赖**:
  - Guzzle 7.x
  - Symfony Process 7.x
  - Illuminate components
  - Ratchet Pawl（用于 WebSocket 支持）

## Composer 安装

由于这是一个分支版本，您需要将仓库添加到您的 `composer.json` 文件中：

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/cuppett/php-k8s"
        }
    ]
}
```

然后通过 Composer 安装该包：

```bash
composer require renoki-co/php-k8s
```

## Laravel 安装

对于 Laravel 项目，您可以使用上游项目的 Laravel 专用包：

```bash
composer require renoki-co/laravel-php-k8s
```

> **注：** [laravel-php-k8s](https://github.com/renoki-co/laravel-php-k8s) 包源自上游项目，其与本分支版本的兼容性不做保证。

安装后，发布配置文件：

```bash
php artisan vendor:publish --tag=k8s-config
```

这将创建一个 `config/k8s.php` 文件，您可以在其中配置默认集群连接。

## 验证安装

创建一个简单的测试脚本以验证安装：

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// 创建集群连接
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// 通过获取命名空间测试连接
try {
    $namespaces = $cluster->getAllNamespaces();
    echo "连接成功！找到 " . count($namespaces) . " 个命名空间。\n";
} catch (\Exception $e) {
    echo "连接失败：" . $e->getMessage() . "\n";
}
```

## 开发安装

如果您正在为该库做贡献或想要运行测试：

```bash
# 克隆仓库
git clone https://github.com/cuppett/php-k8s.git
cd php-k8s

# 安装依赖
composer install

# 运行测试（需要运行中的 Kubernetes 集群）
vendor/bin/phpunit

# 运行静态分析
vendor/bin/psalm
```

## 下一步

现在您已经安装了 PHP K8s，您可以：

- [快速开始指南](/zh/guide/getting-started/quickstart) - 通过实践教程学习基础知识
- [认证](/zh/guide/getting-started/authentication) - 配置到集群的认证
- [配置](/zh/guide/getting-started/configuration) - 探索配置选项

## 故障排除

### SSL 证书错误

如果您在连接到集群时遇到 SSL 证书错误：

```php
$cluster = new KubernetesCluster('https://your-cluster-url');

// 禁用 SSL 验证（不建议在生产环境中使用）
$cluster->withoutSslChecks();
```

对于生产环境，请正确配置 SSL 证书。

### 未找到 YAML 扩展

YAML 扩展是可选的，但推荐使用。要安装它：

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS（通过 Homebrew）
brew install php
pecl install yaml

# 或者使用基于数组的资源定义代替 YAML
```

### 内存限制问题

对于大规模操作，您可能需要增加 PHP 的内存限制：

```php
ini_set('memory_limit', '512M');
```

或者在您的 `php.ini` 中：

```ini
memory_limit = 512M
```

---

*源自 renoki-co/php-k8s 官方文档，已适配 cuppett/php-k8s 分支版本*
