# JSON Merge Patch

JSON Merge Patch (RFC 7396) support for simple resource updates.

## Overview

JSON Merge Patch provides simple merging updates:

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('metadata.labels.version', 'v2.0')
    ->remove('metadata.labels.deprecated');

$deployment->jsonMergePatch($patch);
```

## Array Format

```php
$patchArray = [
    'spec' => ['replicas' => 5],
    'metadata' => [
        'labels' => [
            'version' => 'v2.0',
            'deprecated' => null  // null removes
        ]
    ]
];

$deployment->jsonMergePatch($patchArray);
```

## See Also

- [Patching Guide](/guide/usage/patching) - Complete patching documentation

---

*JSON Merge Patch API reference for cuppett/php-k8s fork*
