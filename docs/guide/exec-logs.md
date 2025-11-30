# Exec & Logs

PHP K8s provides WebSocket-based methods for executing commands in containers and streaming logs in real-time.

## Execute Commands

Execute commands in a pod's container:

```php
// Execute in the first container
$output = $pod->exec(['ls', '-la', '/var/www']);

foreach ($output as $line) {
    echo $line . "\n";
}

// Execute in a specific container (multi-container pods)
$output = $pod->exec(['ls', '-la'], 'nginx');
```

### Interactive Commands

```php
// Run a shell command
$output = $pod->exec(['/bin/sh', '-c', 'echo "Hello from pod"']);

// Check if a file exists
$output = $pod->exec(['/bin/sh', '-c', 'test -f /app/config.yaml && echo "exists"']);
```

### Error Handling

```php
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

try {
    $output = $pod->exec(['invalid-command']);
} catch (KubernetesAPIException $e) {
    echo "Exec failed: {$e->getMessage()}";
}
```

## Stream Logs

### Simple Log Retrieval

Get all logs from a container:

```php
// Get logs from the first container
$logs = $pod->logs();

echo $logs;

// Get logs from specific container
$nginxLogs = $pod->containerLogs('nginx');
```

### Watch Logs in Real-Time

Stream logs as they're produced:

```php
$pod->watchLogs(function ($line) {
    echo "[LOG] {$line}\n";

    // Return true to stop watching
    return false;
});

// Watch logs from specific container
$pod->watchContainerLogs('nginx', function ($line) {
    echo "[NGINX] {$line}\n";
    return false;
});
```

### Log Options

Retrieve logs with options:

```php
// Get last 100 lines
$logs = $pod->logs([
    'tailLines' => 100,
]);

// Get logs since specific time
$logs = $pod->logs([
    'sinceSeconds' => 3600, // Last hour
]);

// Include timestamps
$logs = $pod->logs([
    'timestamps' => true,
]);

// Get previous container logs (if restarted)
$logs = $pod->logs([
    'previous' => true,
]);
```

## Advanced Examples

### Wait for Pod and Get Logs

```php
function waitAndGetLogs(string $podName, string $namespace = 'default'): string
{
    global $cluster;

    // Wait for pod to be running
    $pod = $cluster->getPodByName($podName, $namespace);

    while ($pod->getPodPhase() !== \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
        sleep(2);
        $pod->refresh();
    }

    // Get logs once running
    return $pod->logs();
}

$logs = waitAndGetLogs('my-job-pod');
echo $logs;
```

### Stream Logs to File

```php
$logFile = fopen('/tmp/pod-logs.txt', 'w');

$pod->watchLogs(function ($line) use ($logFile) {
    fwrite($logFile, $line . "\n");
    return false; // Continue watching
});

fclose($logFile);
```

### Execute Health Check

```php
function checkPodHealth($pod): array
{
    $results = [];

    // Check if main process is running
    $output = $pod->exec(['pgrep', '-f', 'nginx']);
    $results['nginx_running'] = !empty($output);

    // Check disk space
    $output = $pod->exec(['df', '-h', '/']);
    $results['disk_info'] = implode("\n", $output);

    // Check memory
    $output = $pod->exec(['free', '-m']);
    $results['memory_info'] = implode("\n", $output);

    return $results;
}

$health = checkPodHealth($pod);
if (!$health['nginx_running']) {
    echo "Warning: nginx not running!";
}
```

### Log Aggregation from Multiple Pods

```php
$pods = $cluster->getAllPods('production');

$allLogs = [];

foreach ($pods as $pod) {
    if ($pod->getPodPhase() === \RenokiCo\PhpK8s\Enums\PodPhase::RUNNING) {
        $logs = $pod->logs(['tailLines' => 50]);
        $allLogs[$pod->getName()] = $logs;
    }
}

// Process aggregated logs
foreach ($allLogs as $podName => $logs) {
    echo "=== Logs from {$podName} ===\n";
    echo $logs . "\n\n";
}
```

### Execute Database Backup

```php
$mysqlPod = $cluster->getPodByName('mysql-0', 'databases');

// Create backup
$backup = $mysqlPod->exec([
    '/bin/sh', '-c',
    'mysqldump -u root -p${MYSQL_ROOT_PASSWORD} --all-databases'
]);

// Save backup to file
file_put_contents('/backups/mysql-' . date('Y-m-d') . '.sql', implode("\n", $backup));

echo "Backup completed successfully!";
```

### Multi-Container Log Monitoring

```php
$pod = $cluster->getPodByName('app-pod');

// Watch logs from all containers in parallel
foreach ($pod->getContainers() as $container) {
    $containerName = $container->getName();

    // Fork or use async for real parallel execution
    $pod->watchContainerLogs($containerName, function ($line) use ($containerName) {
        echo "[{$containerName}] {$line}\n";
        return false;
    });
}
```

### Execute Debugging Commands

```php
function debugPod($pod): array
{
    $debug = [];

    // Get environment variables
    $debug['env'] = $pod->exec(['env']);

    // Get running processes
    $debug['processes'] = $pod->exec(['ps', 'aux']);

    // Get network connections
    $debug['network'] = $pod->exec(['netstat', '-tulpn']);

    // Get disk usage
    $debug['disk'] = $pod->exec(['du', '-sh', '/var/*']);

    return $debug;
}

$debugInfo = debugPod($pod);
foreach ($debugInfo as $category => $output) {
    echo "=== {$category} ===\n";
    echo implode("\n", $output) . "\n\n";
}
```

## Best Practices

1. **Check pod status first** - Ensure pod is running before exec/logs
2. **Handle errors gracefully** - Wrap in try-catch blocks
3. **Limit log tail** - Use `tailLines` to avoid excessive data
4. **Use specific containers** - Specify container name in multi-container pods
5. **Stream large outputs** - Use watch for real-time data instead of buffering
6. **Set timeouts** - Prevent hanging connections
7. **Close resources** - Clean up file handles and connections

## Limitations

- Exec requires container to have shell (`/bin/sh` or `/bin/bash`)
- Log streaming uses WebSockets (requires network stability)
- Large log outputs may consume significant memory
- Some containers may not support exec (distroless images)

## Troubleshooting

### Exec Not Working

```php
// Check if container has shell
try {
    $output = $pod->exec(['which', 'sh']);
    echo "Shell available: " . implode('', $output);
} catch (\Exception $e) {
    echo "No shell available in container";
}
```

### Logs Not Appearing

```php
// Check pod status
$pod->refresh();
echo "Pod phase: {$pod->getPodPhase()->value}\n";

// Check container status
if (!$pod->containersAreReady()) {
    echo "Containers not ready yet\n";
}

// Try with timestamps
$logs = $pod->logs(['timestamps' => true]);
```

## Related Features

- [Watching Resources](/guide/watching-resources) - Monitor resource changes
- [Pod Resource](/resources/workloads/pod) - Complete pod documentation

## Next Steps

- [Patching](/guide/patching) - Update resources with JSON Patch
- [Scaling](/guide/scaling) - Scale deployments and statefulsets

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
