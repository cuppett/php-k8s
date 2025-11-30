# Laravel Integration

Using PHP K8s with Laravel applications.

## Installation

```bash
composer require renoki-co/laravel-php-k8s
```

## Configuration

Publish configuration:

```bash
php artisan vendor:publish --tag=k8s-config
```

Edit `config/k8s.php`:

```php
return [
    'default' => env('K8S_CLUSTER', 'default'),

    'connections' => [
        'default' => [
            'url' => env('K8S_URL', 'http://127.0.0.1:8080'),
            'auth' => [
                'type' => env('K8S_AUTH_TYPE', 'kubeconfig'),
                'kubeconfig' => env('K8S_KUBECONFIG'),
                'token' => env('K8S_TOKEN'),
            ],
        ],
    ],
];
```

## Usage

```php
use RenokiCo\LaravelK8s\LaravelK8sFacade as K8s;

// Use default connection
$pods = K8s::getAllPods();

// Use specific connection
$pods = K8s::connection('production')->getAllPods();
```

## Environment Variables

```env
K8S_URL=https://kubernetes.example.com:6443
K8S_AUTH_TYPE=token
K8S_TOKEN=your-service-account-token
```

## See Also

- [Configuration](/getting-started/configuration) - Configuration details
- [laravel-php-k8s Package](https://github.com/renoki-co/laravel-php-k8s)

---

*Laravel integration guide for cuppett/php-k8s fork*
