# Base Resource

All Kubernetes resources in PHP K8s extend the `K8sResource` base class and compose functionality using traits.

## Resource Structure

Every resource follows this pattern:

```php
class K8sResourceType extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;
    // Other traits...

    protected static $kind = 'ResourceType';
    protected static $defaultVersion = 'v1';
    protected static $namespaceable = true;
}
```

## Common Properties

All resources have:

- **metadata** - Name, namespace, labels, annotations
- **spec** - Desired state specification
- **status** - Current state (read-only from cluster)

## Common Methods

### Metadata

```php
$resource->setName('my-resource');
$resource->setNamespace('production');
$resource->setLabels(['app' => 'web']);
$resource->setAnnotations(['description' => 'My resource']);
```

### Spec Management

```php
$resource->setAttribute('spec.replicas', 3);
$value = $resource->getAttribute('spec.replicas');
```

### CRUD Operations

```php
$resource->create();
$resource->update();
$resource->delete();
$resource->refresh();
```

## Available Resource Types

See the sidebar for documentation on specific resource types:

- **Workloads**: Pod, Deployment, StatefulSet, DaemonSet, Job, CronJob
- **Configuration**: ConfigMap, Secret
- **Storage**: PersistentVolume, PersistentVolumeClaim, StorageClass
- **Networking**: Service, Ingress, NetworkPolicy
- **Autoscaling**: HorizontalPodAutoscaler, VerticalPodAutoscaler
- **Policy**: ResourceQuota, LimitRange, PodDisruptionBudget
- **RBAC**: ServiceAccount, Role, RoleBinding, ClusterRole, ClusterRoleBinding
- **Cluster**: Namespace, Node, Event

## See Also

- [K8sResource API](/development/api-reference/k8s-resource) - Base class documentation
- [Resource Model](/development/architecture/resource-model) - Architecture details
- [Pod](/resources/workloads/pod) - Example resource documentation

---

*Base resource documentation for cuppett/php-k8s fork*
