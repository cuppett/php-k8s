# Import from YAML

PHP K8s supports importing Kubernetes resources from YAML files, making it easy to work with existing manifests or integrate with GitOps workflows.

## Prerequisites

::: warning YAML Extension Required
For YAML imports to work, you need the `ext-yaml` PHP extension installed:

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS (via PECL)
pecl install yaml

# Verify installation
php -m | grep yaml
```
:::

## Basic Import Methods

PHP K8s provides two primary methods for importing YAML:

```php
// Import from YAML string
$resource = $cluster->fromYaml($yamlString);

// Import from YAML file
$resource = $cluster->fromYamlFile('/path/to/manifest.yaml');
```

## Single Resource Import

Import a single resource from YAML:

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: ConfigMap
metadata:
  name: app-config
  namespace: default
data:
  DATABASE_HOST: mysql.example.com
  DATABASE_PORT: "3306"
YAML;

$configMap = $cluster->fromYaml($yamlContent);

// The resource is loaded but not created yet
$configMap->isSynced(); // false

// Create it in the cluster
$configMap->create();

echo "Created ConfigMap: {$configMap->getName()}";
```

## Multiple Resource Import

When a YAML file contains multiple resources (separated by `---`), the method returns an array:

```php
$yamlContent = <<<YAML
apiVersion: v1
kind: Namespace
metadata:
  name: production
---
apiVersion: v1
kind: Namespace
metadata:
  name: staging
---
apiVersion: v1
kind: Namespace
metadata:
  name: development
YAML;

$namespaces = $cluster->fromYaml($yamlContent);

// $namespaces is an array of K8sNamespace instances
foreach ($namespaces as $ns) {
    $ns->createOrUpdate();
    echo "{$ns->getName()} namespace synced!\n";
}
```

## Importing from Files

Import resources from YAML files:

```php
// Single resource file
$service = $cluster->fromYamlFile('/path/to/service.yaml');
$service->create();

// Multiple resources file
$resources = $cluster->fromYamlFile('/path/to/manifests.yaml');

foreach ($resources as $resource) {
    $resource->createOrUpdate();
    echo "Synced: {$resource->getName()} ({$resource->getKind()})\n";
}
```

## Templated YAML Import

For dynamic YAML with variable substitution, use templated imports:

### YAML Template Example

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: "{app_name}-config"
  namespace: "{namespace}"
  labels:
    app: "{app_name}"
    environment: "{environment}"
data:
  DATABASE_HOST: "{db_host}"
  DATABASE_PORT: "{db_port}"
  CACHE_DRIVER: "{cache_driver}"
```

### PHP Implementation

```php
$cm = $cluster->fromTemplatedYamlFile('/path/to/configmap-template.yaml', [
    'app_name' => 'myapp',
    'namespace' => 'production',
    'environment' => 'prod',
    'db_host' => 'mysql.prod.example.com',
    'db_port' => '3306',
    'cache_driver' => 'redis',
]);

$cm->create();
```

### Template Syntax

- Use curly brackets `{}` to define template variables
- Variable names should match the keys in the replacement array
- Works with any YAML field (metadata, spec, data, etc.)

```php
// Template with multiple resources
$resources = $cluster->fromTemplatedYamlFile('/path/to/deployment-template.yaml', [
    'app_name' => 'api-server',
    'replicas' => '3',
    'image_tag' => 'v2.1.0',
    'cpu_limit' => '500m',
    'memory_limit' => '512Mi',
]);

foreach ($resources as $resource) {
    $resource->createOrUpdate();
}
```

## Working with Imported Resources

After importing, resources behave like any other PHP K8s resource:

```php
$pod = $cluster->fromYamlFile('/path/to/pod.yaml');

// Modify before creating
$pod->setNamespace('custom-namespace')
    ->setLabels(['imported' => 'true', 'source' => 'yaml']);

// Create in cluster
$pod->create();

// Check status
if ($pod->isSynced()) {
    echo "Pod created successfully!";
}
```

## Advanced Examples

### Import and Modify

```php
$deployment = $cluster->fromYamlFile('/path/to/deployment.yaml');

// Override values
$deployment
    ->setNamespace('production')
    ->setReplicas(5)
    ->setLabels(array_merge(
        $deployment->getLabels(),
        ['managed-by' => 'php-k8s']
    ))
    ->createOrUpdate();
```

### Conditional Creation

```php
$resources = $cluster->fromYamlFile('/path/to/manifests.yaml');

foreach ($resources as $resource) {
    // Only create services and deployments
    if (in_array($resource->getKind(), ['Service', 'Deployment'])) {
        $resource->createOrUpdate();
        echo "Created {$resource->getKind()}: {$resource->getName()}\n";
    }
}
```

### Dynamic Template Generation

```php
function deployApplication(string $environment, array $config): void
{
    global $cluster;

    $resources = $cluster->fromTemplatedYamlFile('/templates/app.yaml', [
        'environment' => $environment,
        'namespace' => $config['namespace'],
        'replicas' => $config['replicas'],
        'image' => $config['image'],
        'domain' => $config['domain'],
    ]);

    foreach ($resources as $resource) {
        $resource->createOrUpdate();
    }
}

// Deploy to production
deployApplication('production', [
    'namespace' => 'prod',
    'replicas' => '5',
    'image' => 'myapp:v2.0.0',
    'domain' => 'api.example.com',
]);
```

## GitOps Integration

Combine YAML imports with version control:

```php
// Clone repository
exec('git clone https://github.com/myorg/k8s-manifests /tmp/manifests');

// Apply all manifests
$files = glob('/tmp/manifests/*.yaml');

foreach ($files as $file) {
    $resources = $cluster->fromYamlFile($file);

    if (is_array($resources)) {
        foreach ($resources as $resource) {
            $resource->createOrUpdate();
        }
    } else {
        $resources->createOrUpdate();
    }

    echo "Applied: " . basename($file) . "\n";
}
```

## Error Handling

Handle YAML parsing and import errors:

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $resource = $cluster->fromYamlFile('/path/to/manifest.yaml');
    $resource->create();
} catch (\Exception $e) {
    if ($e instanceof KubernetesAPIException) {
        echo "API Error: " . $e->getMessage();
    } else {
        echo "YAML Error: " . $e->getMessage();
    }
}
```

## Best Practices

1. **Validate YAML** - Ensure YAML is valid before importing
2. **Use templates for dynamic configs** - Keep manifests DRY
3. **Check resource types** - Verify imported resources are expected types
4. **Version your manifests** - Store in version control
5. **Test in dev first** - Always test YAML imports in development
6. **Handle arrays** - Check if return value is array or single resource

## Limitations

- Requires `ext-yaml` PHP extension
- Template syntax is basic (no conditionals or loops)
- Large YAML files may consume significant memory

## Alternatives

If you can't install `ext-yaml`, build resources programmatically:

```php
// Instead of YAML import
$pod = K8s::pod($cluster)
    ->setName('nginx')
    ->setNamespace('default')
    ->setContainers([
        K8s::container()
            ->setName('nginx')
            ->setImage('nginx:latest')
    ])
    ->create();
```

## Next Steps

- [CRUD Operations](/guide/usage/crud-operations) - Manage imported resources
- [Patching](/guide/usage/patching) - Update resources with JSON Patch
- [Custom Resources](/guide/usage/custom-resources) - Import CRDs from YAML

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
