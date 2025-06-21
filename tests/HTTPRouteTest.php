<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sHTTPRoute;
use RenokiCo\PhpK8s\ResourcesList;

class HTTPRouteTest extends TestCase
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
        'api.example.com',
        'www.example.com',
    ];

    /**
     * The default testing rules.
     *
     * @var array
     */
    protected static $rules = [[
        'matches' => [[
            'path' => [
                'type' => 'PathPrefix',
                'value' => '/api',
            ],
        ]],
        'backendRefs' => [[
            'name' => 'api-service',
            'port' => 80,
            'weight' => 100,
        ]],
    ]];

    public function test_http_route_build()
    {
        $route = $this->cluster->httpRoute()
            ->setName('example-http-route')
            ->setLabels(['tier' => 'routing'])
            ->setAnnotations(['route/type' => 'api'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $this->assertEquals(self::$parentRefs, $route->getParentRefs());
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function test_http_route_from_yaml_post()
    {
        $route = $this->cluster->fromYamlFile(__DIR__.'/yaml/http-route.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $this->assertEquals(self::$parentRefs, $route->getParentRefs());
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function test_http_route_api_interaction()
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
        $route = $this->cluster->httpRoute()
            ->setName('example-http-route')
            ->setLabels(['tier' => 'routing'])
            ->setAnnotations(['route/type' => 'api'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertFalse($route->isSynced());
        $this->assertFalse($route->exists());

        $route = $route->createOrUpdate();

        $this->assertTrue($route->isSynced());
        $this->assertTrue($route->exists());

        $this->assertInstanceOf(K8sHTTPRoute::class, $route);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $this->assertEquals(self::$parentRefs, $route->getParentRefs());
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runGetAllTests()
    {
        $httpRoutes = $this->cluster->getAllHttpRoutes();

        $this->assertInstanceOf(ResourcesList::class, $httpRoutes);

        foreach ($httpRoutes as $route) {
            $this->assertInstanceOf(K8sHTTPRoute::class, $route);

            $this->assertNotNull($route->getName());
        }
    }

    public function runGetAllFromAllNamespacesTests()
    {
        $httpRoutes = $this->cluster->getAllHttpRoutesFromAllNamespaces();

        $this->assertInstanceOf(ResourcesList::class, $httpRoutes);

        foreach ($httpRoutes as $route) {
            $this->assertInstanceOf(K8sHTTPRoute::class, $route);

            $this->assertNotNull($route->getName());
        }
    }

    public function runGetTests()
    {
        $route = $this->cluster->getHttpRouteByName('example-http-route');

        $this->assertInstanceOf(K8sHTTPRoute::class, $route);

        $this->assertTrue($route->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $this->assertEquals(self::$parentRefs, $route->getParentRefs());
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runUpdateTests()
    {
        $route = $this->cluster->getHttpRouteByName('example-http-route');

        $this->assertTrue($route->isSynced());

        $route->setAnnotations([]);

        $route->createOrUpdate();

        $this->assertTrue($route->isSynced());

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals([], $route->getAnnotations());
        $this->assertEquals(self::$parentRefs, $route->getParentRefs());
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $this->assertEquals(self::$rules, $route->getRules());
    }

    public function runDeletionTests()
    {
        $httpRoute = $this->cluster->getHttpRouteByName('example-http-route');

        $this->assertTrue($httpRoute->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getHttpRouteByName('example-http-route');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->httpRoute()->watchAll(function ($type, $httpRoute) {
            if ($httpRoute->getName() === 'example-http-route') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->httpRoute()->watchByName('example-http-route', function ($type, $httpRoute) {
            return $httpRoute->getName() === 'example-http-route';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}