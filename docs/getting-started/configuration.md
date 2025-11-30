# Configuration

This guide covers advanced configuration options for PHP K8s.

## Cluster Configuration

### Basic Setup

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');
```

### HTTP Client Options

Configure Guzzle HTTP client options:

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443', [
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => true,
    'http_errors' => true,
]);
```

### Custom Headers

Add custom headers to all requests:

```php
$cluster->setHeaders([
    'X-Custom-Header' => 'value',
    'User-Agent' => 'MyApp/1.0',
]);
```

## SSL/TLS Configuration

### Custom CA Certificate

```php
$cluster->withCaCertificate('/path/to/ca.crt');
```

### Client Certificates

```php
$cluster->withCertificate(
    '/path/to/client.crt',
    '/path/to/client.key',
    '/path/to/ca.crt'
);
```

### Disable SSL Verification

::: danger Development Only
Never disable SSL verification in production!
:::

```php
$cluster->withoutSslChecks();
```

## Timeout Configuration

Configure different timeout values:

```php
// Connection timeout (default: 10 seconds)
$cluster->setConnectionTimeout(15);

// Request timeout (default: 30 seconds)
$cluster->setTimeout(60);

// For long-running operations
$cluster->setTimeout(300); // 5 minutes
```

## Namespace Configuration

Set a default namespace for all operations:

```php
$cluster->setDefaultNamespace('my-app');

// All operations will use 'my-app' namespace by default
$pods = $cluster->getAllPods(); // Gets pods from 'my-app'
```

## Laravel Configuration

When using the Laravel package, publish and edit `config/k8s.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Cluster Connection
    |--------------------------------------------------------------------------
    */
    'default' => env('K8S_CLUSTER', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Cluster Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'default' => [
            'url' => env('K8S_URL', 'http://127.0.0.1:8080'),
            'auth' => [
                'type' => env('K8S_AUTH_TYPE', 'kubeconfig'),
                'kubeconfig' => env('K8S_KUBECONFIG', null),
                'context' => env('K8S_CONTEXT', null),
                'token' => env('K8S_TOKEN', null),
            ],
            'ssl' => [
                'verify' => env('K8S_SSL_VERIFY', true),
                'ca' => env('K8S_SSL_CA', null),
                'cert' => env('K8S_SSL_CERT', null),
                'key' => env('K8S_SSL_KEY', null),
            ],
            'timeout' => env('K8S_TIMEOUT', 30),
            'connect_timeout' => env('K8S_CONNECT_TIMEOUT', 10),
        ],

        'production' => [
            'url' => env('K8S_PROD_URL'),
            'auth' => [
                'type' => 'token',
                'token' => env('K8S_PROD_TOKEN'),
            ],
            'ssl' => [
                'verify' => true,
                'ca' => env('K8S_PROD_CA'),
            ],
        ],
    ],
];
```

Then use in your Laravel application:

```php
use RenokiCo\LaravelK8s\LaravelK8sFacade as K8s;

// Use default connection
$pods = K8s::getAllPods();

// Use specific connection
$pods = K8s::connection('production')->getAllPods();
```

## Environment Variables

Common environment variables for configuration:

```env
# Cluster URL
K8S_URL=https://kubernetes.example.com:6443

# Authentication
K8S_AUTH_TYPE=token
K8S_TOKEN=your-service-account-token

# Or use kubeconfig
K8S_AUTH_TYPE=kubeconfig
K8S_KUBECONFIG=/path/to/kubeconfig.yaml
K8S_CONTEXT=my-context

# SSL
K8S_SSL_VERIFY=true
K8S_SSL_CA=/path/to/ca.crt
K8S_SSL_CERT=/path/to/client.crt
K8S_SSL_KEY=/path/to/client.key

# Timeouts
K8S_TIMEOUT=30
K8S_CONNECT_TIMEOUT=10

# Default namespace
K8S_NAMESPACE=default
```

## Logging

Enable request/response logging for debugging:

```php
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// Add logging middleware
$cluster->setHttpClientOptions([
    'handler' => $stack,
    'debug' => true, // Enable debug mode
]);
```

For Laravel, use the built-in logging:

```php
use Illuminate\Support\Facades\Log;

// Log will automatically include K8s operations
Log::channel('k8s')->info('Creating pod', ['name' => 'my-pod']);
```

## Retry Configuration

Configure automatic retries for failed requests:

```php
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$cluster = new KubernetesCluster('https://kubernetes.example.com:6443');

// Retry up to 3 times with exponential backoff
$cluster->setHttpClientOptions([
    'retry_on_status' => [429, 503],
    'retry_count' => 3,
    'retry_delay' => 1000, // milliseconds
]);
```

## Rate Limiting

Implement rate limiting to avoid overwhelming the API server:

```php
use RenokiCo\PhpK8s\RateLimiter;

$rateLimiter = new RateLimiter(
    maxRequests: 100,
    perSeconds: 60
);

$cluster->setRateLimiter($rateLimiter);
```

## Resource Defaults

Set default values for resources:

```php
K8s::pod()->setDefaults([
    'imagePullPolicy' => 'Always',
    'restartPolicy' => 'Always',
]);

// All pods will have these defaults
$pod = K8s::pod($cluster)
    ->setName('my-pod')
    ->setContainers([...])
    ->create(); // imagePullPolicy and restartPolicy are set automatically
```

## Multi-Cluster Configuration

Manage multiple clusters efficiently:

```php
class ClusterManager
{
    private array $clusters = [];

    public function __construct()
    {
        $this->clusters = [
            'prod-us' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-us-east-1'),
            'prod-eu' => KubernetesCluster::fromKubeConfigYamlFile(null, 'prod-eu-west-1'),
            'staging' => KubernetesCluster::fromKubeConfigYamlFile(null, 'staging'),
            'dev' => new KubernetesCluster('http://127.0.0.1:8080'),
        ];
    }

    public function get(string $name): KubernetesCluster
    {
        return $this->clusters[$name] ?? throw new \InvalidArgumentException("Cluster {$name} not found");
    }

    public function all(): array
    {
        return $this->clusters;
    }
}

// Usage
$manager = new ClusterManager();
$prodPods = $manager->get('prod-us')->getAllPods();
```

## Performance Tuning

### Connection Pooling

Reuse connections for better performance:

```php
$cluster = new KubernetesCluster('https://kubernetes.example.com:6443', [
    'http_version' => '2.0', // Use HTTP/2 if supported
]);
```

### Batch Operations

Batch operations when possible:

```php
// Instead of creating pods one by one
$pods = [];
for ($i = 0; $i < 10; $i++) {
    $pods[] = K8s::pod($cluster)
        ->setName("pod-{$i}")
        ->setContainers([...])
        ->toArray();
}

// Create all at once using kubectl apply
K8s::fromYaml($cluster, yaml_emit($pods))->create();
```

### Caching

Implement caching for frequently accessed resources:

```php
use Illuminate\Support\Facades\Cache;

$namespaces = Cache::remember('k8s.namespaces', 300, function () use ($cluster) {
    return $cluster->getAllNamespaces();
});
```

## Next Steps

- [CRUD Operations](/guide/crud-operations) - Start managing resources
- [Authentication](/getting-started/authentication) - Configure secure access
- [Examples](/examples/basic-crud) - See configuration in action

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
