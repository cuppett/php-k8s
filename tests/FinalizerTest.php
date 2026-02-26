<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;

class FinalizerTest extends TestCase
{
    public function test_set_and_get_finalizers()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->setFinalizers(['test/finalizer1', 'test/finalizer2']);

        $this->assertEquals(['test/finalizer1', 'test/finalizer2'], $cm->getFinalizers());
    }

    public function test_add_finalizer()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->addFinalizer('test/finalizer1')
            ->addFinalizer('test/finalizer2');

        $this->assertEquals(['test/finalizer1', 'test/finalizer2'], $cm->getFinalizers());
    }

    public function test_add_duplicate_is_idempotent()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->addFinalizer('test/finalizer1')
            ->addFinalizer('test/finalizer1');

        $this->assertEquals(['test/finalizer1'], $cm->getFinalizers());
    }

    public function test_remove_finalizer()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->setFinalizers(['test/finalizer1', 'test/finalizer2', 'test/finalizer3'])
            ->removeFinalizer('test/finalizer2');

        $this->assertEquals(['test/finalizer1', 'test/finalizer3'], $cm->getFinalizers());
    }

    public function test_remove_nonexistent_finalizer()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->setFinalizers(['test/finalizer1'])
            ->removeFinalizer('test/nonexistent');

        $this->assertEquals(['test/finalizer1'], $cm->getFinalizers());
    }

    public function test_has_finalizer()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->setFinalizers(['test/finalizer1', 'test/finalizer2']);

        $this->assertTrue($cm->hasFinalizer('test/finalizer1'));
        $this->assertTrue($cm->hasFinalizer('test/finalizer2'));
        $this->assertFalse($cm->hasFinalizer('test/nonexistent'));
    }

    public function test_configmap_with_finalizer_from_yaml()
    {
        $cm = $this->cluster->fromYamlFile(__DIR__.'/yaml/configmap-with-finalizer.yaml');

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('test-configmap-with-finalizer', $cm->getName());
        $this->assertEquals(['test/cleanup'], $cm->getFinalizers());
        $this->assertEquals(['key1' => 'value1'], $cm->getData());
    }

    public function test_finalizer_api_interaction()
    {
        $this->runCreationTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm-with-finalizer')
            ->setLabels(['test-name' => 'finalizer-test'])
            ->setData(['key' => 'value'])
            ->addFinalizer('test/cleanup');

        $this->assertFalse($cm->isSynced());
        $this->assertFalse($cm->exists());

        $cm = $cm->createOrUpdate();

        $this->assertTrue($cm->isSynced());
        $this->assertTrue($cm->exists());

        $this->assertInstanceOf(K8sConfigMap::class, $cm);

        $this->assertEquals('test-cm-with-finalizer', $cm->getName());
        $this->assertTrue($cm->hasFinalizer('test/cleanup'));

        // Refresh and verify finalizer persists.
        $cm = $cm->refresh();
        $this->assertTrue($cm->hasFinalizer('test/cleanup'));
    }

    public function runDeletionTests()
    {
        // Try to delete - should enter Terminating state.
        $cm = $this->cluster->getConfigMapByName('test-cm-with-finalizer');
        $cm->delete();

        // Allow a brief moment for deletion to be processed.
        sleep(1);

        // Resource should still exist because of the finalizer.
        $cm = $this->cluster->getConfigMapByName('test-cm-with-finalizer');
        $this->assertTrue($cm->exists());

        // Remove finalizer and update.
        $cm->removeFinalizer('test/cleanup')->update();

        // Wait for deletion to complete.
        sleep(2);

        // Now it should be gone.
        $this->expectException(KubernetesAPIException::class);
        $this->cluster->getConfigMapByName('test-cm-with-finalizer');
    }
}
