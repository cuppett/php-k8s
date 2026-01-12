---
layout: home

hero:
  name: PHP K8s
  text: Kubernetes 的 PHP 客户端
  tagline: 使用这款现代化的基于PHP的客户端来管控你的 Kubernetes 集群。该客户端支持执行 API、CRDs 以及任意形式的身份认证。
  actions:
    - theme: brand
      text: 快速开始
      link: /zh/guide/getting-started/installation
    - theme: alt
      text: 资源
      link: /zh/resources/base-resource
    - theme: alt
      text: 示例
      link: /zh/guide/usage/cluster-interaction

features:
  - icon: 🚀
    title: 全面支持 Kubernetes API
    details: 可与33种以上的资源类型进行交互，包括 Pods、Deployments、Services、ConfigMaps 等。

  - icon: 📝
    title: 现代化的 PHP 8.2+
    details: 基于 PHP 8.2 及以上版本特性构建，包括枚举、类型提示、匹配表达式和只读属性。

  - icon: 🔧
    title: 极简的 CRUD 操作
    details: 简洁易用的 Kubernetes 资源增删改查 API

  - icon: 👀
    title: 监听 & 流式传输
    details: 通过 Watch API 实时监听资源，流式传输容器日志并在容器内执行命令。

  - icon: 🔐
    title: 灵活的认证
    details: 支持 kubeconfig 文件、令牌、证书、集群内配置以及自定义认证。

  - icon: 🎯
    title: JSON Patch 支持
    details: 全面支持 RFC 6902（JSON Patch）和 RFC 7396（JSON Merge Patch），可实现对资源的精准更新。

  - icon: 🔌
    title: 自定义资源定义 (CRDs)
    details: 借助宏和动态注册，可轻松与 CRD 实现集成。

  - icon: 📦
    title: Laravel 合集
    details: 通过 laravel-php-k8s 包提供一流的 Laravel 框架支持，实现与框架的无缝集成。

  - icon: 📄
    title: YAML 导入
    details: 支持动态值模板，可直接从 YAML 文件中导入资源。

  - icon: ⚡
    title: 执行 & 日志
    details: 通过 WebSocket 连接在容器中执行命令并实时流式传输日志。

  - icon: 📊
    title: 自动扩缩容
    details: 管理水平和垂直 Pod 自动扩缩器，以实现基于指标的自动扩缩。

  - icon: 🛡️
    title: RBAC 权限管理
    details: 全面支持基于角色的访问控制，涵盖 ServiceAccount、Role 及 Binding 功能。
---

## 快速示例

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// 连接集群
$cluster = new KubernetesCluster('https://127.0.0.1:8443');

// 创建 Pod
$pod = K8s::pod($cluster)
    ->setName('my-app')
    ->setNamespace('default')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('nginx:latest')
            ->setPorts([
                K8s::containerPort()->setContainerPort(80)
            ])
    ])
    ->create();

echo $pod->getName(); // my-app
echo $pod->getPodPhase(); // PodPhase::RUNNING
```

## Fork 说明

> **注：** 这是 [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s) 项目的一个维护分支，提供了对 PHP 8.2 及以上版本的增强支持，并新增了多项功能。

创建此 fork 的目的是为了持续推进该库的活跃开发与现代化改造。本仓库与上游项目的核心差异如下：

- **PHP 8.2+ 现代化适配**: 全面运用枚举类型、只读属性及现代类型注解
- **持续维护保障**: 针对 Kubernetes 新版本提供定期更新
- **增强型资源支持**: 新增更多资源类型并优化 trait 组合逻辑
- **完备文档体系**: 此文档站点配备了丰富的示例与使用指南

如需了解与上游项目的详细对比，可查阅 [Fork 差异](/zh/project/fork-differences) 页面。

## 安装

由于这是一个 fork 版本，请将本仓库添加到您的 `composer.json`:

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

随后安装 composer 包:

```bash
composer require renoki-co/php-k8s
```

对于 Laravel 项目:

```bash
composer require renoki-co/laravel-php-k8s
```

> **注：** [laravel-php-k8s](https://github.com/renoki-co/laravel-php-k8s) 包源自上游项目，其与本分支版本的兼容性不做保证。

## 支持的 Kubernetes 版本

该库已针对多个 Kubernetes 版本完成测试：

- **v1.32.9** ✅
- **v1.33.5** ✅
- **v1.34.1** ✅

## 要求

- PHP 8.2 或更高
- ext-json
- Guzzle 7.x
- Symfony Process 7.x

## 社区与支持

- **GitHub Issues**: [Report bugs or request features](https://github.com/cuppett/php-k8s/issues)
- **上游项目**: [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s)
- **上游文档**: [Original GitBook Documentation](https://php-k8s.renoki.org/)

## 鸣谢

- **原始作者**: [Alex Renoki](https://github.com/rennokki)
- **Fork 维护者**: [Stephen Cuppett](https://github.com/cuppett)
- **所有贡献者**: [Contributors](https://github.com/cuppett/php-k8s/graphs/contributors)

## License

本项目在 [Apache-2.0 License](https://github.com/cuppett/php-k8s/blob/main/LICENSE) 下分发。
