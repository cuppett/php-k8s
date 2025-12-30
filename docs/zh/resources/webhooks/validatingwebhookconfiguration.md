# ValidatingWebhookConfiguration

ValidatingWebhookConfigurations define admission webhooks that validate requests.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$webhook = K8s::validatingWebhookConfiguration($cluster)
    ->setName('pod-policy')
    ->setWebhooks([
        [
            'name' => 'pod-policy.example.com',
            'clientConfig' => [
                'service' => [
                    'name' => 'webhook-service',
                    'namespace' => 'default',
                    'path' => '/validate',
                ],
            ],
            'rules' => [
                [
                    'apiGroups' => [''],
                    'apiVersions' => ['v1'],
                    'operations' => ['CREATE'],
                    'resources' => ['pods'],
                ],
            ],
        ],
    ])
    ->create();
```

::: info
ValidatingWebhookConfiguration is cluster-scoped (not namespaced).
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
