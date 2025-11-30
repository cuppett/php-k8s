# Installation

## Requirements

Before installing PHP K8s, ensure your environment meets these requirements:

- **PHP**: 8.2 or higher (8.3+ recommended)
- **Extensions**:
  - `ext-json` (required)
  - `ext-yaml` (optional, but recommended for YAML support)
- **Dependencies**:
  - Guzzle 7.x
  - Symfony Process 7.x
  - Illuminate components
  - Ratchet Pawl (for WebSocket support)

## Composer Installation

Install the package via Composer:

```bash
composer require renoki-co/php-k8s
```

## Laravel Installation

For Laravel projects, use the Laravel-specific package:

```bash
composer require renoki-co/laravel-php-k8s
```

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=k8s-config
```

This creates a `config/k8s.php` file where you can configure default cluster connections.

## Verify Installation

Create a simple test script to verify the installation:

```php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// Create a cluster connection
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Test the connection by fetching namespaces
try {
    $namespaces = $cluster->getAllNamespaces();
    echo "Successfully connected! Found " . count($namespaces) . " namespaces.\n";
} catch (\Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
```

## Development Installation

If you're contributing to the library or want to run tests:

```bash
# Clone the repository
git clone https://github.com/cuppett/php-k8s.git
cd php-k8s

# Install dependencies
composer install

# Run tests (requires a running Kubernetes cluster)
vendor/bin/phpunit

# Run static analysis
vendor/bin/psalm
```

## Next Steps

Now that you've installed PHP K8s, you can:

- [Quick Start Guide](/getting-started/quickstart) - Learn the basics with a hands-on tutorial
- [Authentication](/getting-started/authentication) - Configure authentication to your cluster
- [Configuration](/getting-started/configuration) - Explore configuration options

## Troubleshooting

### SSL Certificate Errors

If you encounter SSL certificate errors when connecting to your cluster:

```php
$cluster = new KubernetesCluster('https://your-cluster-url');

// Disable SSL verification (NOT recommended for production)
$cluster->withoutSslChecks();
```

For production environments, properly configure SSL certificates instead.

### YAML Extension Not Found

The YAML extension is optional but recommended. To install it:

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS (via Homebrew)
brew install php
pecl install yaml

# Or use array-based resource definitions instead of YAML
```

### Memory Limit Issues

For large-scale operations, you might need to increase PHP's memory limit:

```php
ini_set('memory_limit', '512M');
```

Or in your `php.ini`:

```ini
memory_limit = 512M
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
