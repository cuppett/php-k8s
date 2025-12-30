# MutatingWebhookConfiguration

MutatingWebhookConfigurations define admission webhooks that can modify requests.

## Basic Usage

```php
use RenokiCo\PhpK8s\K8s;

$webhook = K8s::mutatingWebhookConfiguration($cluster)
    ->setName('pod-defaults')
    ->setWebhooks([
        [
            'name' => 'pod-defaults.example.com',
            'clientConfig' => [
                'service' => [
                    'name' => 'webhook-service',
                    'namespace' => 'default',
                    'path' => '/mutate',
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
MutatingWebhookConfiguration is cluster-scoped (not namespaced).
:::

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
