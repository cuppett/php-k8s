---
layout: home

hero:
  name: PHP K8s
  text: PHP Client for Kubernetes
  tagline: Control your Kubernetes clusters with this modern PHP-based client. Supports exec API, CRDs, and any form of authentication.
  image:
    src: /logo.png
    alt: PHP K8s
  actions:
    - theme: brand
      text: Get Started
      link: /getting-started/installation
    - theme: alt
      text: View on GitHub
      link: https://github.com/cuppett/php-k8s
    - theme: alt
      text: Examples
      link: /examples/basic-crud

features:
  - icon: 🚀
    title: Full Kubernetes API Support
    details: Interact with 33+ resource types including Pods, Deployments, Services, ConfigMaps, and more.

  - icon: 📝
    title: Modern PHP 8.2+
    details: Built with PHP 8.2+ features including enums, type hints, match expressions, and readonly properties.

  - icon: 🔧
    title: Easy CRUD Operations
    details: Simple, intuitive API for creating, reading, updating, and deleting Kubernetes resources.

  - icon: 👀
    title: Watch & Stream
    details: Watch resources in real-time with the Watch API. Stream logs and execute commands in containers.

  - icon: 🔐
    title: Flexible Authentication
    details: Support for kubeconfig files, tokens, certificates, in-cluster config, and custom authentication.

  - icon: 🎯
    title: JSON Patch Support
    details: Full RFC 6902 (JSON Patch) and RFC 7396 (JSON Merge Patch) support for precise resource updates.

  - icon: 🔌
    title: Custom Resources (CRDs)
    details: Easy integration with Custom Resource Definitions using macros and dynamic registration.

  - icon: 📦
    title: Laravel Integration
    details: First-class Laravel support via the laravel-php-k8s package for seamless framework integration.

  - icon: 📄
    title: YAML Import
    details: Import resources directly from YAML files with template support for dynamic values.

  - icon: ⚡
    title: Exec & Logs
    details: Execute commands in containers and stream logs in real-time via WebSocket connections.

  - icon: 📊
    title: Autoscaling
    details: Manage Horizontal and Vertical Pod Autoscalers for automatic scaling based on metrics.

  - icon: 🛡️
    title: RBAC Management
    details: Full support for Role-Based Access Control with ServiceAccounts, Roles, and Bindings.
---

## Quick Example

```php
<?php

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// Connect to your cluster
$cluster = new KubernetesCluster('https://127.0.0.1:8443');

// Create a pod
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

## Fork Information

> **Note:** This is a maintained fork of [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s) with enhanced PHP 8.2+ support and additional features.

This fork was created to continue active development and modernization of the library. Key differences from the upstream project:

- **PHP 8.2+ Modernization**: Full use of enums, readonly properties, and modern type hints
- **Active Maintenance**: Regular updates for new Kubernetes versions
- **Enhanced Resource Support**: Additional resource types and improved trait composition
- **Comprehensive Documentation**: This documentation site with extensive examples and guides

See the [Fork Differences](/project/fork-differences) page for a detailed comparison with the upstream project.

## Installation

```bash
composer require renoki-co/php-k8s
```

For Laravel projects:

```bash
composer require renoki-co/laravel-php-k8s
```

## Supported Kubernetes Versions

This library is tested against multiple Kubernetes versions:

- **v1.32.9** ✅
- **v1.33.5** ✅
- **v1.34.1** ✅

## Requirements

- PHP 8.2 or higher
- ext-json
- Guzzle 7.x
- Symfony Process 7.x

## Community & Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/cuppett/php-k8s/issues)
- **Upstream Project**: [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s)
- **Upstream Docs**: [Original GitBook Documentation](https://php-k8s.renoki.org/)

## Credits

- **Original Author**: [Alex Renoki](https://github.com/rennokki)
- **Fork Maintainer**: [Stephen Cuppett](https://github.com/cuppett)
- **All Contributors**: [Contributors](https://github.com/cuppett/php-k8s/graphs/contributors)

## License

This project is licensed under the [Apache-2.0 License](https://github.com/cuppett/php-k8s/blob/main/LICENSE).
