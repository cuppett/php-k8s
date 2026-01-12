# CI/CD Integration

Using PHP K8s in CI/CD pipelines.

## GitHub Actions

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install

      - name: Deploy to Kubernetes
        env:
          K8S_TOKEN: ${{ secrets.K8S_TOKEN }}
          K8S_URL: ${{ secrets.K8S_URL }}
        run: php deploy.php
```

## Deployment Script

```php
<?php
// deploy.php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster($_ENV['K8S_URL']);
$cluster->withToken($_ENV['K8S_TOKEN']);

$deployment = K8s::deployment($cluster)
    ->setName('my-app')
    ->setNamespace('production')
    ->setReplicas(3)
    // ... configure deployment
    ->createOrUpdate();

echo "Deployed: {$deployment->getName()}\n";
```

## See Also

- [Configuration](/guide/getting-started/configuration) - Configuration options

---

*CI/CD integration guide for cuppett/php-k8s fork*
