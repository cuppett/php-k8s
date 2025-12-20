# State Tracking

How PHP K8s tracks resource synchronization state.

## Resource States

Resources track two states:

- **Synced** (`isSynced()`) - Resource has been synced with cluster
- **Exists** (`exists()`) - Resource currently exists in cluster

## Lifecycle

```php
$pod = K8s::pod($cluster)->setName('test');

$pod->isSynced();  // false - not yet created
$pod->exists();    // false - doesn't exist

$pod->create();

$pod->isSynced();  // true - synced with cluster
$pod->exists();    // true - exists in cluster

$pod->delete();

$pod->exists();    // false - no longer exists
```

## Use Cases

- **Idempotency** - Use `createOrUpdate()` for safe repeated operations
- **Validation** - Check state before operations
- **Cleanup** - Verify deletion success

---

*State tracking documentation for cuppett/php-k8s fork*
