<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Test\Kinds\Gateway;
use RenokiCo\PhpK8s\ResourcesList;

class GatewayTest extends TestCase
{
    /**
     * The default testing listeners.
     *
     * @var array
     */
    protected static $listeners = [[
        'name' => 'http-listener',
        'hostname' => 'gateway.example.com',
        'port' => 80,
        'protocol' => 'HTTP',
    ]];

    /**
     * The default testing addresses.
     *
     * @var array
     */
    protected static $addresses = [[
        'type' => 'IPAddress',
        'value' => '192.168.1.100',
    ]];

    public function test_gateway_build()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('example-gateway')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/type' => 'load-balancer'])
            ->setGatewayClassName('example-gateway-class')
            ->setListeners(self::$listeners)
            ->setAddresses(self::$addresses);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
        $this->assertEquals(self::$addresses, $gw->getAddresses());
    }

    public function test_gateway_from_yaml_post()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->fromYamlFile(__DIR__.'/yaml/gateway.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
    }

    public function test_gateway_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetTests();
        $this->runUpdateTests();
    }

    public function runCreationTests()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('example-gateway')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/type' => 'load-balancer'])
            ->setGatewayClassName('example-gateway-class')
            ->setListeners(self::$listeners)
            ->setAddresses(self::$addresses);

        $this->assertFalse($gw->isSynced());
        $this->assertFalse($gw->exists());

        $gw = $gw->createOrUpdate();

        $this->assertTrue($gw->isSynced());
        $this->assertTrue($gw->exists());

        $this->assertInstanceOf(Gateway::class, $gw);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
        $this->assertEquals(self::$addresses, $gw->getAddresses());
    }

    public function runGetAllTests()
    {
        Gateway::register('gateway');

        $gateways = $this->cluster->getAllGateways();

        $this->assertInstanceOf(ResourcesList::class, $gateways);

        foreach ($gateways as $gw) {
            $this->assertInstanceOf(Gateway::class, $gw);

            $this->assertNotNull($gw->getName());
        }
    }

    public function runGetAllFromAllNamespacesTests()
    {
        Gateway::register('gateway');

        $gateways = $this->cluster->getAllGatewaysFromAllNamespaces();

        $this->assertInstanceOf(ResourcesList::class, $gateways);

        foreach ($gateways as $gw) {
            $this->assertInstanceOf(Gateway::class, $gw);

            $this->assertNotNull($gw->getName());
        }
    }

    public function runGetTests()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->getGatewayByName('example-gateway');

        $this->assertInstanceOf(Gateway::class, $gw);

        $this->assertTrue($gw->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
    }

    public function runUpdateTests()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->getGatewayByName('example-gateway');

        $this->assertTrue($gw->isSynced());

        $gw->setAnnotations([]);

        $gw->createOrUpdate();

        $this->assertTrue($gw->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals([], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
        $this->assertEquals(self::$addresses, $gw->getAddresses());
    }

    public function runDeletionTests()
    {
        Gateway::register('gateway');

        $gateway = $this->cluster->getGatewayByName('example-gateway');

        $this->assertTrue($gateway->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getGatewayByName('example-gateway');
    }

    public function runWatchAllTests()
    {
        Gateway::register('gateway');

        $watch = $this->cluster->gateway()->watchAll(function ($type, $gateway) {
            if ($gateway->getName() === 'example-gateway') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        Gateway::register('gateway');

        $watch = $this->cluster->gateway()->watchByName('example-gateway', function ($type, $gateway) {
            return $gateway->getName() === 'example-gateway';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}