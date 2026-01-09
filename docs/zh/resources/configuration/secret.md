# Secret

Secrets store sensitive data like passwords, tokens, and keys.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$secret = K8s::secret($cluster)
    ->setName('db-credentials')
    ->setNamespace('default')
    ->setData('username', base64_encode('admin'))
    ->setData('password', base64_encode('secret123'))
    ->create();
```

::: warning
Secret data must be base64 encoded.
:::

## Use in Pod

```php
$pod = K8s::pod($cluster)
    ->setName('app-pod')
    ->setContainers([
        K8s::container()
            ->setName('app')
            ->setImage('myapp:latest')
            ->addSecretKeyRef('DB_USER', 'db-credentials', 'username')
            ->addSecretKeyRef('DB_PASS', 'db-credentials', 'password')
    ])
    ->create();
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
