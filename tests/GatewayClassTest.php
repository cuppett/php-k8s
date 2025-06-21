<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sGatewayClass;
use RenokiCo\PhpK8s\ResourcesList;

class GatewayClassTest extends TestCase
{
    public function test_gateway_class_build()
    {
        $gwc = $this->cluster->gatewayClass()
            ->setName('example-gateway-class')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/controller' => 'example-controller'])
            ->setControllerName('example.com/gateway-controller')
            ->setDescription('Example gateway class for testing');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
        $this->assertEquals('Example gateway class for testing', $gwc->getDescription());
    }

    public function test_gateway_class_from_yaml_post()
    {
        $gwc = $this->cluster->fromYamlFile(__DIR__.'/yaml/gateway-class.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
    }

    public function test_gateway_class_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetAllFromAllNamespacesTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $gwc = $this->cluster->gatewayClass()
            ->setName('example-gateway-class')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/controller' => 'example-controller'])
            ->setControllerName('example.com/gateway-controller')
            ->setDescription('Example gateway class for testing');

        $this->assertFalse($gwc->isSynced());
        $this->assertFalse($gwc->exists());

        $gwc = $gwc->createOrUpdate();

        $this->assertTrue($gwc->isSynced());
        $this->assertTrue($gwc->exists());

        $this->assertInstanceOf(K8sGatewayClass::class, $gwc);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
        $this->assertEquals('Example gateway class for testing', $gwc->getDescription());
    }

    public function runGetAllTests()
    {
        $gatewayClasses = $this->cluster->getAllGatewayClasses();

        $this->assertInstanceOf(ResourcesList::class, $gatewayClasses);

        foreach ($gatewayClasses as $gwc) {
            $this->assertInstanceOf(K8sGatewayClass::class, $gwc);

            $this->assertNotNull($gwc->getName());
        }
    }

    public function runGetAllFromAllNamespacesTests()
    {
        $gatewayClasses = $this->cluster->getAllGatewayClassesFromAllNamespaces();

        $this->assertInstanceOf(ResourcesList::class, $gatewayClasses);

        foreach ($gatewayClasses as $gwc) {
            $this->assertInstanceOf(K8sGatewayClass::class, $gwc);

            $this->assertNotNull($gwc->getName());
        }
    }

    public function runGetTests()
    {
        $gwc = $this->cluster->getGatewayClassByName('example-gateway-class');

        $this->assertInstanceOf(K8sGatewayClass::class, $gwc);

        $this->assertTrue($gwc->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
    }

    public function runUpdateTests()
    {
        $gwc = $this->cluster->getGatewayClassByName('example-gateway-class');

        $this->assertTrue($gwc->isSynced());

        $gwc->setAnnotations([]);

        $gwc->createOrUpdate();

        $this->assertTrue($gwc->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals([], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
        $this->assertEquals('Example gateway class for testing', $gwc->getDescription());
    }

    public function runDeletionTests()
    {
        $gatewayClass = $this->cluster->getGatewayClassByName('example-gateway-class');

        $this->assertTrue($gatewayClass->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getGatewayClassByName('example-gateway-class');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->gatewayClass()->watchAll(function ($type, $gatewayClass) {
            if ($gatewayClass->getName() === 'example-gateway-class') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->gatewayClass()->watchByName('example-gateway-class', function ($type, $gatewayClass) {
            return $gatewayClass->getName() === 'example-gateway-class';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}