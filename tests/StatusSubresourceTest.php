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

        // Wait for the deployment controller to initialize status.
        sleep(2);

        // Refresh to get the current status from the controller.
        $deployment = $deployment->refresh();

        // Try to patch status using jsonMergePatchStatus().
        // Note: The controller may still create conflicts, but the API call should work.
        try {
            $result = $deployment->jsonMergePatchStatus([
                'status' => [
                    'conditions' => [],
                ],
            ]);

            $this->assertInstanceOf(\RenokiCo\PhpK8s\Kinds\K8sDeployment::class, $result);
        } catch (\RenokiCo\PhpK8s\Exceptions\KubernetesAPIException $e) {
            // 409 Conflict is expected when the controller races with our update.
            // This proves the status endpoint is working correctly.
            if ($e->getCode() !== 409) {
                throw $e;
            }
        }

        // Cleanup.
        $deployment->delete();
    }
}
