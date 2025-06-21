<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Test\Kinds\GRPCRoute;
use RenokiCo\PhpK8s\ResourcesList;

class GRPCRouteTest extends TestCase
{
    /**
     * The default testing parent references.
     *
     * @var array
     */
    protected static $parentRefs = [[
        'name' => 'example-gateway',
        'namespace' => 'default',
    ]];

    /**
     * The default testing hostnames.
     *
     * @var array
     */
    protected static $hostnames = [
        'grpc.example.com',
    ];

    /**
     * The default testing rules.
     *
     * @var array
     */
    protected static $rules = [[
        'matches' => [[
            'method' => [
                'service' => 'example.service',
                'method' => 'GetUser',
            ],
        ]],
        'backendRefs' => [[
            'name' => 'grpc-service',
            'port' => 9090,
            'weight' => 100,
        ]],
    ]];

    public function test_grpc_route_build()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('example-grpc-route')
            ->setLabels(['tier' => 'grpc'])
            ->setAnnotations(['route/type' => 'grpc'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function test_grpc_route_from_yaml_post()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->fromYamlFile(__DIR__.'/yaml/grpc-route.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function test_grpc_route_api_interaction()
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
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('example-grpc-route')
            ->setLabels(['tier' => 'grpc'])
            ->setAnnotations(['route/type' => 'grpc'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertFalse($route->isSynced());
        $this->assertFalse($route->exists());

        $route = $route->createOrUpdate();

        $this->assertTrue($route->isSynced());
        $this->assertTrue($route->exists());

        $this->assertInstanceOf(GRPCRoute::class, $route);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runGetAllTests()
    {
        GRPCRoute::register('grpcRoute');

        $grpcRoutes = $this->cluster->getAllGrpcRoutes();

        $this->assertInstanceOf(ResourcesList::class, $grpcRoutes);

        foreach ($grpcRoutes as $route) {
            $this->assertInstanceOf(GRPCRoute::class, $route);

            $this->assertNotNull($route->getName());
        }
    }

    public function runGetAllFromAllNamespacesTests()
    {
        GRPCRoute::register('grpcRoute');

        $grpcRoutes = $this->cluster->getAllGrpcRoutesFromAllNamespaces();

        $this->assertInstanceOf(ResourcesList::class, $grpcRoutes);

        foreach ($grpcRoutes as $route) {
            $this->assertInstanceOf(GRPCRoute::class, $route);

            $this->assertNotNull($route->getName());
        }
    }

    public function runGetTests()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->getGrpcRouteByName('example-grpc-route');

        $this->assertInstanceOf(GRPCRoute::class, $route);

        $this->assertTrue($route->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runUpdateTests()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->getGrpcRouteByName('example-grpc-route');

        $this->assertTrue($route->isSynced());

        $route->setAnnotations([]);

        $route->createOrUpdate();

        $this->assertTrue($route->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals([], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runDeletionTests()
    {
        GRPCRoute::register('grpcRoute');

        $grpcRoute = $this->cluster->getGrpcRouteByName('example-grpc-route');

        $this->assertTrue($grpcRoute->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getGrpcRouteByName('example-grpc-route');
    }

    public function runWatchAllTests()
    {
        GRPCRoute::register('grpcRoute');

        $watch = $this->cluster->grpcRoute()->watchAll(function ($type, $grpcRoute) {
            if ($grpcRoute->getName() === 'example-grpc-route') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        GRPCRoute::register('grpcRoute');

        $watch = $this->cluster->grpcRoute()->watchByName('example-grpc-route', function ($type, $grpcRoute) {
            return $grpcRoute->getName() === 'example-grpc-route';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}