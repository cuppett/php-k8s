# Documentation Helper Scripts

Scripts to help maintain documentation as the codebase evolves.

## Available Scripts

### check-documentation.php

Verify that all resource classes have corresponding documentation.

```bash
php scripts/check-documentation.php
```

**Output:**
```
✓ Documented Resources: 33
  - K8sPod
  - K8sDeployment
  ...

⚠️  Undocumented Resources: 2
  - K8sNewResource (newresource)
  - K8sAnotherResource (anotherresource)
```

**Use Case:** Run before releases to ensure complete documentation coverage.

### generate-resource-doc.php

Generate documentation stub for a new resource type.

```bash
php scripts/generate-resource-doc.php <ClassName> <category>
```

**Example:**
```bash
php scripts/generate-resource-doc.php K8sDeployment workloads
```

**Categories:**
- `cluster` - Namespace, Node, Event
- `workloads` - Pod, Deployment, StatefulSet, DaemonSet, Job, CronJob, ReplicaSet
- `configuration` - ConfigMap, Secret
- `storage` - PersistentVolume, PersistentVolumeClaim, StorageClass
- `networking` - Service, Ingress, NetworkPolicy, EndpointSlice
- `autoscaling` - HorizontalPodAutoscaler, VerticalPodAutoscaler
- `policy` - ResourceQuota, LimitRange, PodDisruptionBudget, PriorityClass
- `rbac` - ServiceAccount, Role, ClusterRole, RoleBinding, ClusterRoleBinding
- `webhooks` - ValidatingWebhookConfiguration, MutatingWebhookConfiguration

**Output:**
- Creates documentation file with template
- Shows next steps for completion
- Provides sidebar configuration snippet

### update-sidebar.php

Scan docs directory and generate sidebar configuration suggestions.

```bash
php scripts/update-sidebar.php
```

**Output:**
```javascript
{
  text: 'Workloads',
  collapsed: true,
  items: [
    { text: 'Pod', link: '/resources/workloads/pod' },
    { text: 'Deployment', link: '/resources/workloads/deployment' },
    ...
  ]
}
```

**Use Case:** After adding multiple new resource docs, regenerate sidebar config.

## Workflow Examples

### Adding a New Resource

```bash
# 1. Implement resource class
vim src/Kinds/K8sNewResource.php

# 2. Add factory method
vim src/Traits/InitializesResources.php

# 3. Write tests
vim tests/NewResourceTest.php

# 4. Generate documentation stub
php scripts/generate-resource-doc.php K8sNewResource policy

# 5. Edit generated documentation
vim docs/resources/policy/newresource.md

# 6. Update sidebar (copy from script output)
php scripts/update-sidebar.php
vim docs/.vitepress/config.mjs

# 7. Build and verify
npm run docs:dev

# 8. Run documentation check
php scripts/check-documentation.php
```

### Pre-Release Documentation Check

```bash
# Check all resources are documented
php scripts/check-documentation.php

# Build documentation
npm run docs:build

# Verify no broken links
# (Manual review or use link checker)
```

## Adding Scripts

When adding new helper scripts:

1. Make executable: `chmod +x scripts/your-script.php`
2. Add shebang: `#!/usr/bin/env php`
3. Document in this README
4. Show usage examples

## See Also

- [Documentation Maintenance](/development/documentation) - Complete maintenance guide
- [Contributing](/development/contributing) - General contribution guidelines
