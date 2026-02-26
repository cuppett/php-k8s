<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sLease;
use RenokiCo\PhpK8s\ResourcesList;

class LeaseTest extends TestCase
{
    public function test_lease_build()
    {
        $lease = $this->cluster->lease()
            ->setName('test-lease')
            ->setLabels(['app' => 'test'])
            ->setHolderIdentity('holder-1')
            ->setLeaseDurationSeconds(15)
            ->setAcquireTime('2024-01-01T00:00:00.000000Z')
            ->setRenewTime('2024-01-01T00:00:15.000000Z');

        $this->assertEquals('coordination.k8s.io/v1', $lease->getApiVersion());
        $this->assertEquals('test-lease', $lease->getName());
        $this->assertEquals(['app' => 'test'], $lease->getLabels());
        $this->assertEquals('holder-1', $lease->getHolderIdentity());
        $this->assertEquals(15, $lease->getLeaseDurationSeconds());
        $this->assertEquals('2024-01-01T00:00:00.000000Z', $lease->getAcquireTime());
        $this->assertEquals('2024-01-01T00:00:15.000000Z', $lease->getRenewTime());
    }

    public function test_lease_from_yaml()
    {
        $lease = $this->cluster->fromYamlFile(__DIR__.'/yaml/lease.yaml');

        $this->assertEquals('coordination.k8s.io/v1', $lease->getApiVersion());
        $this->assertEquals('test-lease', $lease->getName());
        $this->assertEquals(['app' => 'test'], $lease->getLabels());
        $this->assertEquals('holder-1', $lease->getHolderIdentity());
        $this->assertEquals(15, $lease->getLeaseDurationSeconds());
    }

    public function test_lease_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $lease = $this->cluster->lease()
            ->setName('test-lease')
            ->setLabels(['test-name' => 'lease'])
            ->setHolderIdentity('test-holder')
            ->setLeaseDurationSeconds(15);

        $this->assertFalse($lease->isSynced());
        $this->assertFalse($lease->exists());

        $lease = $lease->createOrUpdate();

        $this->assertTrue($lease->isSynced());
        $this->assertTrue($lease->exists());

        $this->assertInstanceOf(K8sLease::class, $lease);

        $this->assertEquals('coordination.k8s.io/v1', $lease->getApiVersion());
        $this->assertEquals('test-lease', $lease->getName());
        $this->assertEquals(['test-name' => 'lease'], $lease->getLabels());
        $this->assertEquals('test-holder', $lease->getHolderIdentity());
        $this->assertEquals(15, $lease->getLeaseDurationSeconds());
    }

    public function runGetAllTests()
    {
        $leases = $this->cluster->getAllLeases();

        $this->assertInstanceOf(ResourcesList::class, $leases);

        foreach ($leases as $lease) {
            $this->assertInstanceOf(K8sLease::class, $lease);

            $this->assertNotNull($lease->getName());
        }
    }

    public function runGetTests()
    {
        $lease = $this->cluster->getLeaseByName('test-lease');

        $this->assertInstanceOf(K8sLease::class, $lease);

        $this->assertTrue($lease->isSynced());

        $this->assertEquals('coordination.k8s.io/v1', $lease->getApiVersion());
        $this->assertEquals('test-lease', $lease->getName());
        $this->assertEquals(['test-name' => 'lease'], $lease->getLabels());
        $this->assertEquals('test-holder', $lease->getHolderIdentity());
        $this->assertEquals(15, $lease->getLeaseDurationSeconds());
    }

    public function runUpdateTests()
    {
        $lease = $this->cluster->getLeaseByName('test-lease');

        $this->assertTrue($lease->isSynced());

        $lease->setHolderIdentity('new-holder');

        $this->assertTrue($lease->update());

        $lease = $this->cluster->getLeaseByName('test-lease');

        $this->assertEquals('new-holder', $lease->getHolderIdentity());
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->lease()->watchAll(function ($type, $lease) {
            if ($lease->getName() === 'test-lease') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->getLeaseByName('test-lease')->watch(function ($type, $lease) {
            return $lease->getName() === 'test-lease';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runDeletionTests()
    {
        $lease = $this->cluster->getLeaseByName('test-lease');

        $this->assertTrue($lease->delete());

        while ($lease->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getLeaseByName('test-lease');
    }
}
