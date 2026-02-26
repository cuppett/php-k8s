<?php

namespace RenokiCo\PhpK8s\Test;

class StatusSubresourceTest extends TestCase
{
    public function test_resource_status_path()
    {
        // Test core resource (v1).
        $pod = $this->cluster->pod()
            ->setName('test-pod')
            ->setNamespace('default');

        $this->assertEquals(
            '/api/v1/namespaces/default/pods/test-pod/status',
            $pod->resourceStatusPath()
        );

        // Test apps resource (apps/v1).
        $deployment = $this->cluster->deployment()
            ->setName('test-deployment')
            ->setNamespace('default');

        $this->assertEquals(
            '/apis/apps/v1/namespaces/default/deployments/test-deployment/status',
            $deployment->resourceStatusPath()
        );

        // Test coordination resource (coordination.k8s.io/v1).
        $lease = $this->cluster->lease()
            ->setName('test-lease')
            ->setNamespace('default');

        $this->assertEquals(
            '/apis/coordination.k8s.io/v1/namespaces/default/leases/test-lease/status',
            $lease->resourceStatusPath()
        );
    }

    public function test_set_and_get_status()
    {
        $deployment = $this->cluster->deployment()
            ->setName('test-deployment')
            ->setStatus('replicas', 3)
            ->setStatus('availableReplicas', 2);

        $this->assertEquals(3, $deployment->getStatus('replicas'));
        $this->assertEquals(2, $deployment->getStatus('availableReplicas'));

        // Test setStatusData and getStatusData.
        $deployment->setStatusData([
            'replicas' => 5,
            'availableReplicas' => 4,
            'readyReplicas' => 4,
        ]);

        $statusData = $deployment->getStatusData();
        $this->assertEquals(5, $statusData['replicas']);
        $this->assertEquals(4, $statusData['availableReplicas']);
        $this->assertEquals(4, $statusData['readyReplicas']);
    }

    public function test_status_update_api_interaction()
    {
        // Create a deployment.
        $deployment = $this->cluster->deployment()
            ->setName('test-status-deployment')
            ->setLabels(['test-name' => 'status-subresource'])
            ->setSelectors(['matchLabels' => ['app' => 'test']])
            ->setReplicas(1)
            ->setTemplate([
                'metadata' => [
                    'labels' => ['app' => 'test'],
                ],
                'spec' => [
                    'containers' => [
                        [
                            'name' => 'nginx',
                            'image' => 'nginx:latest',
                        ],
                    ],
                ],
            ])
            ->createOrUpdate();

        $this->assertTrue($deployment->isSynced());
        $this->assertTrue($deployment->exists());

        // Try to update status using updateStatus().
        // Note: The controller will likely overwrite our changes,
        // but we're just verifying the API call succeeds.
        $deployment->setStatus('observedGeneration', 1);
        $result = $deployment->updateStatus();

        $this->assertInstanceOf(\RenokiCo\PhpK8s\Kinds\K8sDeployment::class, $result);

        // Try to patch status using jsonMergePatchStatus().
        $result = $deployment->jsonMergePatchStatus([
            'status' => [
                'observedGeneration' => 2,
            ],
        ]);

        $this->assertInstanceOf(\RenokiCo\PhpK8s\Kinds\K8sDeployment::class, $result);

        // Cleanup.
        $deployment->delete();
    }
}
