# RBAC Setup

Examples for setting up Role-Based Access Control.

## Create Service Account

```php
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$sa = K8s::serviceAccount($cluster)
    ->setName('app-sa')
    ->setNamespace('default')
    ->create();
```

## Create Role

```php
$role = K8s::role($cluster)
    ->setName('pod-reader')
    ->setNamespace('default')
    ->setRules([
        [
            'apiGroups' => [''],
            'resources' => ['pods'],
            'verbs' => ['get', 'list', 'watch'],
        ],
    ])
    ->create();
```

## Create RoleBinding

```php
$roleBinding = K8s::roleBinding($cluster)
    ->setName('app-pod-reader')
    ->setNamespace('default')
    ->setRole('pod-reader')
    ->setSubjects([
        [
            'kind' => 'ServiceAccount',
            'name' => 'app-sa',
            'namespace' => 'default',
        ],
    ])
    ->create();
```

## Cluster-Wide Permissions

```php
// ClusterRole
$clusterRole = K8s::clusterRole($cluster)
    ->setName('cluster-admin-custom')
    ->setRules([
        [
            'apiGroups' => ['*'],
            'resources' => ['*'],
            'verbs' => ['*'],
        ],
    ])
    ->create();

// ClusterRoleBinding
$clusterRoleBinding = K8s::clusterRoleBinding($cluster)
    ->setName('app-cluster-admin')
    ->setClusterRole('cluster-admin-custom')
    ->setSubjects([
        [
            'kind' => 'ServiceAccount',
            'name' => 'app-sa',
            'namespace' => 'default',
        ],
    ])
    ->create();
```

---

*RBAC setup example for cuppett/php-k8s fork*
