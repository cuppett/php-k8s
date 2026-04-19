PHP K8s
=======

> **Note:** This is a maintained fork of [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s) with PHP 8.3+ support and additional features. See [fork differences](https://php-k8s.cuppett.dev/project/fork-differences) for details.

![v1.33.10 K8s Version](https://img.shields.io/badge/K8s%20v1.33.10-Ready-%23326ce5?colorA=306CE8&colorB=green)
![v1.34.6 K8s Version](https://img.shields.io/badge/K8s%20v1.34.6-Ready-%23326ce5?colorA=306CE8&colorB=green)
![v1.35.3 K8s Version](https://img.shields.io/badge/K8s%20v1.35.3-Ready-%23326ce5?colorA=306CE8&colorB=green)

[![Client Capabilities](https://img.shields.io/badge/Kubernetes%20Client-Silver-blue.svg?colorB=C0C0C0&colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-capabilities)
[![Client Support Level](https://img.shields.io/badge/Kubernetes%20Client-stable-green.svg?colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-support-level)

Control your Kubernetes clusters with this PHP-based Kubernetes client. It supports any form of authentication, the exec API, and it has an easy implementation for CRDs.

For Laravel projects, you might want to use [renoki-co/laravel-php-k8s](https://github.com/renoki-co/laravel-php-k8s) (from the upstream project). Note that compatibility with this fork is not guaranteed.

## ✨ Features

- **Full Kubernetes API Support**: 33+ resource types including Pods, Deployments, Services, and more
- **Exec & Logs**: Execute commands and stream logs from containers
- **Watch API**: Real-time event streaming for resource changes
- **JSON Patch & Merge Patch**: RFC 6902 and RFC 7396 support for precise updates
- **Custom Resources (CRDs)**: Easy CRD integration with macros
- **PHP 8.2+ Modern Features**: Enums, type hints, readonly properties, match expressions
- **Laravel Integration**: First-class Laravel support via laravel-php-k8s
- **Flexible Authentication**: Kubeconfig, tokens, certificates, exec plugins, AWS EKS native, OpenShift OAuth
- **YAML Import**: Load resources directly from YAML files with templating

## 🚀 Quick Start

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setContainers([
        K8s::container()->setName('app')->setImage('nginx:latest')
    ])
    ->create();

echo $pod->getName(); // my-pod
echo $pod->getPodPhase(); // Returns PodPhase enum
```

[See more examples →](https://php-k8s.cuppett.dev/guide/usage/cluster-interaction)

## 🔐 Advanced Authentication

### AWS EKS Native

```php
// No AWS CLI required - pure PHP token generation
$cluster = KubernetesCluster::fromUrl($eksClusterUrl)
    ->withEksAuth('cluster-name', 'us-east-2')
    ->withCaCertificate('/path/to/ca.crt');
```

### OpenShift OAuth

```php
// Direct username/password authentication
$cluster = KubernetesCluster::fromUrl($openshiftUrl)
    ->withOpenShiftAuth('username', 'password');
```

### Exec Credential Plugins

```php
// Automatically supported from kubeconfig
$cluster = KubernetesCluster::fromKubeConfigYamlFile('~/.kube/config');
// Works with AWS EKS, GKE, AKS, and custom credential providers
```

[See authentication docs →](https://php-k8s.cuppett.dev/guide/getting-started/authentication)

## 📦 Requirements

- PHP 8.2 or higher
- Laravel 11.x or 12.x (for Laravel integration)
- Kubernetes cluster access

## 📃 Documentation

[Read the full documentation at php-k8s.cuppett.dev](https://php-k8s.cuppett.dev/)

This fork is based on [renoki-co/php-k8s](https://github.com/renoki-co/php-k8s). See the [project history](https://php-k8s.cuppett.dev/project/history) and [upstream documentation](https://php-k8s.renoki.org/) for more details.

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email steve@cuppett.com instead of using the issue tracker.

## 🎉 Credits

- **Original Author**: [Alex Renoki](https://github.com/rennokki)
- **Fork Maintainer**: [Stephen Cuppett](https://github.com/cuppett)
- [All Contributors](../../contributors)

