# JSON Patch

JSON Patch (RFC 6902) support for precise resource updates.

## Overview

JSON Patch allows surgical updates to resources with validation:

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

$patch = new JsonPatch();
$patch
    ->test('/metadata/name', 'my-deployment')
    ->replace('/spec/replicas', 5)
    ->add('/metadata/labels/version', 'v2.0')
    ->remove('/metadata/labels/old-label');

$deployment->jsonPatch($patch);
```

## Operations

- `add(path, value)` - Add value
- `remove(path)` - Remove value
- `replace(path, value)` - Replace value
- `move(from, to)` - Move value
- `copy(from, to)` - Copy value
- `test(path, value)` - Verify value

## See Also

- [Patching Guide](/guide/usage/patching) - Complete patching documentation

---

*JSON Patch API reference for cuppett/php-k8s fork*
