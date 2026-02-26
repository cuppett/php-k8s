<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Kinds\K8sConfigMap;

class OwnerReferenceTest extends TestCase
{
    public function test_set_and_get_owner_references()
    {
        $cm = $this->cluster->configMap()
            ->setName('test-cm')
            ->setOwnerReferences([
                [
                    'apiVersion' => 'v1',
                    'kind' => 'Pod',
                    'name' => 'test-pod',
                    'uid' => 'abc-123',
                ],
            ]);

        $refs = $cm->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertEquals('v1', $refs[0]['apiVersion']);
        $this->assertEquals('Pod', $refs[0]['kind']);
        $this->assertEquals('test-pod', $refs[0]['name']);
        $this->assertEquals('abc-123', $refs[0]['uid']);
    }

    public function test_add_owner_reference()
    {
        $owner = $this->cluster->configMap()
            ->setName('owner-cm')
            ->setAttribute('metadata.uid', 'owner-uid-123');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner);

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertEquals('v1', $refs[0]['apiVersion']);
        $this->assertEquals('ConfigMap', $refs[0]['kind']);
        $this->assertEquals('owner-cm', $refs[0]['name']);
        $this->assertEquals('owner-uid-123', $refs[0]['uid']);
    }

    public function test_add_owner_reference_with_controller_flag()
    {
        $owner = $this->cluster->configMap()
            ->setName('owner-cm')
            ->setAttribute('metadata.uid', 'owner-uid-123');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner, controller: true);

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertTrue($refs[0]['controller']);
    }

    public function test_add_owner_reference_with_block_deletion_flag()
    {
        $owner = $this->cluster->configMap()
            ->setName('owner-cm')
            ->setAttribute('metadata.uid', 'owner-uid-123');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner, blockOwnerDeletion: true);

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertTrue($refs[0]['blockOwnerDeletion']);
    }

    public function test_add_duplicate_is_idempotent()
    {
        $owner = $this->cluster->configMap()
            ->setName('owner-cm')
            ->setAttribute('metadata.uid', 'owner-uid-123');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner)
            ->addOwnerReference($owner);

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
    }

    public function test_remove_owner_reference()
    {
        $owner1 = $this->cluster->configMap()
            ->setName('owner-cm-1')
            ->setAttribute('metadata.uid', 'owner-uid-1');

        $owner2 = $this->cluster->configMap()
            ->setName('owner-cm-2')
            ->setAttribute('metadata.uid', 'owner-uid-2');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner1)
            ->addOwnerReference($owner2)
            ->removeOwnerReference($owner1);

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertEquals('owner-uid-2', $refs[0]['uid']);
    }

    public function test_has_owner_reference()
    {
        $owner1 = $this->cluster->configMap()
            ->setName('owner-cm-1')
            ->setAttribute('metadata.uid', 'owner-uid-1');

        $owner2 = $this->cluster->configMap()
            ->setName('owner-cm-2')
            ->setAttribute('metadata.uid', 'owner-uid-2');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner1);

        $this->assertTrue($child->hasOwnerReference($owner1));
        $this->assertFalse($child->hasOwnerReference($owner2));
    }

    public function test_get_controller_owner()
    {
        $owner1 = $this->cluster->configMap()
            ->setName('owner-cm-1')
            ->setAttribute('metadata.uid', 'owner-uid-1');

        $owner2 = $this->cluster->configMap()
            ->setName('owner-cm-2')
            ->setAttribute('metadata.uid', 'owner-uid-2');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner1)
            ->addOwnerReference($owner2, controller: true);

        $controller = $child->getControllerOwner();
        $this->assertNotNull($controller);
        $this->assertEquals('owner-uid-2', $controller['uid']);
    }

    public function test_get_controller_owner_returns_null_when_none()
    {
        $owner = $this->cluster->configMap()
            ->setName('owner-cm')
            ->setAttribute('metadata.uid', 'owner-uid-123');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner);

        $controller = $child->getControllerOwner();
        $this->assertNull($controller);
    }

    public function test_add_owner_reference_requires_uid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource must have a UID');

        $owner = $this->cluster->configMap()
            ->setName('owner-cm');

        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->addOwnerReference($owner);
    }

    public function test_owner_reference_api_interaction()
    {
        $this->runCreationTests();
    }

    public function runCreationTests()
    {
        // Create parent ConfigMap.
        $parent = $this->cluster->configMap()
            ->setName('parent-cm')
            ->setLabels(['test-name' => 'owner-reference-test'])
            ->setData(['parent-key' => 'parent-value'])
            ->createOrUpdate();

        $this->assertTrue($parent->isSynced());
        $this->assertTrue($parent->exists());
        $this->assertNotNull($parent->getAttribute('metadata.uid'));

        // Create child ConfigMap with owner reference.
        $child = $this->cluster->configMap()
            ->setName('child-cm')
            ->setLabels(['test-name' => 'owner-reference-test'])
            ->setData(['child-key' => 'child-value'])
            ->addOwnerReference($parent)
            ->createOrUpdate();

        $this->assertTrue($child->isSynced());
        $this->assertTrue($child->exists());

        $this->assertInstanceOf(K8sConfigMap::class, $child);

        // Verify owner reference persists.
        $child = $child->refresh();
        $this->assertTrue($child->hasOwnerReference($parent));

        $refs = $child->getOwnerReferences();
        $this->assertCount(1, $refs);
        $this->assertEquals('v1', $refs[0]['apiVersion']);
        $this->assertEquals('ConfigMap', $refs[0]['kind']);
        $this->assertEquals('parent-cm', $refs[0]['name']);
        $this->assertEquals($parent->getAttribute('metadata.uid'), $refs[0]['uid']);

        // Cleanup.
        $child->delete();
        $parent->delete();
    }
}
