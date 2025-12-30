# Node

Nodes are worker machines in Kubernetes.

## List Nodes

```php
$nodes = $cluster->getAllNodes();

foreach ($nodes as $node) {
    echo "Node: {$node->getName()}\n";
}
```

## Get Node Details

```php
$node = $cluster->getNodeByName('worker-1');

$node->refresh();

// Node is cluster-scoped (not namespaced)
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
