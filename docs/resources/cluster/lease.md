# Lease

Leases enable distributed systems coordination, primarily used for leader election in high-availability controllers and operators.

## What is a Lease?

A Lease is a lightweight resource in the `coordination.k8s.io/v1` API group that represents a time-based lock. Controllers use leases to:
- Elect a single active leader from multiple replicas
- Coordinate work distribution
- Implement distributed locking

## Creating a Lease

```php
$lease = $cluster->lease()
    ->setName('my-controller-leader')
    ->setNamespace('default')
    ->setHolderIdentity('controller-replica-1')
    ->setLeaseDurationSeconds(15)
    ->setAcquireTime(gmdate('Y-m-d\TH:i:s.u\Z'))
    ->setRenewTime(gmdate('Y-m-d\TH:i:s.u\Z'))
    ->create();
```

## Getting a Lease

```php
$lease = $cluster->getLeaseByName('my-controller-leader', 'default');

echo "Holder: " . $lease->getHolderIdentity() . "\n";
echo "Duration: " . $lease->getLeaseDurationSeconds() . " seconds\n";
echo "Renewed: " . $lease->getRenewTime() . "\n";
```

## Listing Leases

```php
// All leases in a namespace
$leases = $cluster->getAllLeases('kube-system');

foreach ($leases as $lease) {
    echo "{$lease->getName()}: {$lease->getHolderIdentity()}\n";
}

// All leases across all namespaces
$allLeases = $cluster->getAllLeasesFromAllNamespaces();
```

## Updating a Lease

Controllers renew leases to maintain leadership:

```php
$lease = $cluster->getLeaseByName('my-controller-leader', 'default');

$lease->setRenewTime(gmdate('Y-m-d\TH:i:s.u\Z'));
$lease->update();
```

## Leader Election Pattern

Here's a complete leader election implementation:

```php
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

class LeaderElection
{
    private KubernetesCluster $cluster;
    private string $leaseName;
    private string $namespace;
    private string $identity;
    private int $leaseDurationSeconds;
    private int $renewIntervalSeconds;
    private bool $isLeader = false;

    public function __construct(
        KubernetesCluster $cluster,
        string $leaseName,
        string $namespace,
        string $identity,
        int $leaseDurationSeconds = 15,
        int $renewIntervalSeconds = 10
    ) {
        $this->cluster = $cluster;
        $this->leaseName = $leaseName;
        $this->namespace = $namespace;
        $this->identity = $identity;
        $this->leaseDurationSeconds = $leaseDurationSeconds;
        $this->renewIntervalSeconds = $renewIntervalSeconds;
    }

    public function tryAcquireOrRenew(): bool
    {
        try {
            $lease = $this->cluster->getLeaseByName($this->leaseName, $this->namespace);

            // Check if lease is held by us or expired
            $holder = $lease->getHolderIdentity();
            $renewTime = $lease->getRenewTime();

            if ($holder === $this->identity) {
                // We hold the lease - renew it
                $this->renewLease($lease);
                $this->isLeader = true;
                return true;
            }

            // Check if lease is expired
            if ($this->isLeaseExpired($renewTime, $lease->getLeaseDurationSeconds())) {
                // Attempt to acquire expired lease
                return $this->acquireLease($lease);
            }

            // Lease is held by someone else and not expired
            $this->isLeader = false;
            return false;

        } catch (KubernetesAPIException $e) {
            if ($e->getCode() === 404) {
                // Lease doesn't exist - create it
                return $this->createLease();
            }
            throw $e;
        }
    }

    private function createLease(): bool
    {
        try {
            $now = gmdate('Y-m-d\TH:i:s.u\Z');

            $this->cluster->lease()
                ->setName($this->leaseName)
                ->setNamespace($this->namespace)
                ->setHolderIdentity($this->identity)
                ->setLeaseDurationSeconds($this->leaseDurationSeconds)
                ->setAcquireTime($now)
                ->setRenewTime($now)
                ->create();

            $this->isLeader = true;
            echo "Acquired new lease\n";
            return true;

        } catch (KubernetesAPIException $e) {
            if ($e->getCode() === 409) {
                // Someone else created it first
                echo "Lost race to create lease\n";
                return false;
            }
            throw $e;
        }
    }

    private function acquireLease($lease): bool
    {
        try {
            $now = gmdate('Y-m-d\TH:i:s.u\Z');

            $lease->setHolderIdentity($this->identity);
            $lease->setAcquireTime($now);
            $lease->setRenewTime($now);
            $lease->update();

            $this->isLeader = true;
            echo "Acquired expired lease\n";
            return true;

        } catch (KubernetesAPIException $e) {
            if ($e->getCode() === 409) {
                // Conflict - someone else acquired it
                echo "Lost race to acquire lease\n";
                return false;
            }
            throw $e;
        }
    }

    private function renewLease($lease): void
    {
        $now = gmdate('Y-m-d\TH:i:s.u\Z');

        $lease->setRenewTime($now);
        $lease->update();

        echo "Renewed lease\n";
    }

    private function isLeaseExpired(string $renewTime, int $durationSeconds): bool
    {
        $renewTimestamp = strtotime($renewTime);
        $expiryTimestamp = $renewTimestamp + $durationSeconds;

        return time() > $expiryTimestamp;
    }

    public function isLeader(): bool
    {
        return $this->isLeader;
    }

    public function run(callable $leaderFunction, callable $followerFunction = null): void
    {
        while (true) {
            if ($this->tryAcquireOrRenew()) {
                echo "[LEADER] Running leader logic\n";
                $leaderFunction();
            } else {
                echo "[FOLLOWER] Running follower logic\n";
                if ($followerFunction) {
                    $followerFunction();
                }
            }

            sleep($this->renewIntervalSeconds);
        }
    }
}

// Usage
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$election = new LeaderElection(
    cluster: $cluster,
    leaseName: 'my-controller',
    namespace: 'default',
    identity: gethostname() . '-' . getmypid(),
    leaseDurationSeconds: 15,
    renewIntervalSeconds: 10
);

$election->run(
    leaderFunction: function() {
        // Leader work
        echo "Performing reconciliation...\n";
        // ... controller logic ...
    },
    followerFunction: function() {
        // Follower work (optional)
        echo "Standing by...\n";
    }
);
```

## Watching Leases

Monitor lease changes in real-time:

```php
// Watch specific lease
$cluster->getLeaseByName('my-controller', 'default')->watch(function ($type, $lease) {
    echo "[$type] Holder: {$lease->getHolderIdentity()}\n";
    return true;  // Continue watching
});

// Watch all leases in namespace
$cluster->lease()->watchAll(function ($type, $lease) {
    if ($type === 'MODIFIED') {
        echo "Lease {$lease->getName()} renewed by {$lease->getHolderIdentity()}\n";
    }
    return true;
}, ['namespace' => 'default']);
```

## Lease Fields

### Holder Identity

The identity of the current lease holder:

```php
$lease->setHolderIdentity('controller-pod-abc123');
$holder = $lease->getHolderIdentity();
```

### Lease Duration

How long the lease is valid (in seconds):

```php
$lease->setLeaseDurationSeconds(15);
$duration = $lease->getLeaseDurationSeconds();
```

### Acquire Time

When the lease was first acquired (MicroTime format):

```php
$lease->setAcquireTime('2024-01-15T10:30:00.123456Z');
$acquireTime = $lease->getAcquireTime();
```

### Renew Time

Last time the lease was renewed (MicroTime format):

```php
$lease->setRenewTime(gmdate('Y-m-d\TH:i:s.u\Z'));
$renewTime = $lease->getRenewTime();
```

### Lease Transitions

Read-only counter of lease holder changes (managed by API server):

```php
$transitions = $lease->getLeaseTransitions();
echo "Lease has changed hands $transitions times\n";
```

## Best Practices

### Choose Appropriate Durations

```php
// Fast failover (high network traffic)
$lease->setLeaseDurationSeconds(5);
$renewIntervalSeconds = 3;

// Balanced (recommended)
$lease->setLeaseDurationSeconds(15);
$renewIntervalSeconds = 10;

// Slow failover (lower network traffic)
$lease->setLeaseDurationSeconds(60);
$renewIntervalSeconds = 45;
```

**Rule of thumb**: Renew interval should be 2/3 of lease duration.

### Use Unique Identities

```php
// Good - unique and identifiable
$identity = gethostname() . '-' . getmypid();
$identity = $podName . '-' . $podNamespace;

// Avoid - not unique
$identity = 'controller';
```

### Handle Transient Failures

```php
$maxRetries = 3;

for ($i = 0; $i < $maxRetries; $i++) {
    try {
        if ($election->tryAcquireOrRenew()) {
            break;
        }
    } catch (KubernetesAPIException $e) {
        if ($i === $maxRetries - 1) {
            throw $e;
        }
        echo "Retrying lease operation...\n";
        sleep(1);
    }
}
```

### Graceful Shutdown

Release the lease on shutdown:

```php
function shutdown($cluster, $leaseName, $namespace, $identity) {
    try {
        $lease = $cluster->getLeaseByName($leaseName, $namespace);

        if ($lease->getHolderIdentity() === $identity) {
            $lease->delete();
            echo "Released lease\n";
        }
    } catch (\Exception $e) {
        echo "Error releasing lease: {$e->getMessage()}\n";
    }
}

register_shutdown_function('shutdown', $cluster, 'my-controller', 'default', $identity);
```

## Common Use Cases

### High-Availability Controller

```php
// Run controller with multiple replicas
// Only the leader performs reconciliation
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

$election = new LeaderElection(
    cluster: $cluster,
    leaseName: 'backup-controller',
    namespace: 'operators',
    identity: getenv('POD_NAME')
);

$election->run(function() use ($cluster) {
    // Only the leader performs backups
    performDatabaseBackup($cluster);
});
```

### Distributed Work Queue

```php
// Multiple workers, one coordinator
$coordinator = new LeaderElection(
    cluster: $cluster,
    leaseName: 'work-coordinator',
    namespace: 'default',
    identity: gethostname()
);

$coordinator->run(
    leaderFunction: function() {
        // Leader distributes work
        assignWorkToWorkers();
    },
    followerFunction: function() {
        // Followers process work
        processAssignedWork();
    }
);
```

---

*Documentation for cuppett/php-k8s fork*
