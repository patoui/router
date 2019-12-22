<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Route;
use Patoui\Router\Router;
use Patoui\Router\Uri;

class RouterTest extends TestCase
{
    /** @test */
    public function can_get_routes()
    {
        // Arrange
        $router = new Router();
        $routeController = new class() {
            public function index()
            {
                //
            }
        };
        $route = new Route('get', '/', get_class($routeController), 'index');
        $router->addRoute($route);

        // Act
        $routes = $router->getRoutes();

        // Assert
        $this->assertEquals(1, count($routes));
        $this->assertEquals($route, $routes[0]);
    }

    /** @test */
    public function can_add_route()
    {
        // Arrange
        $routeController = new class() {
            public function index()
            {
                //
            }
        };
        $route = new Route('get', '/', get_class($routeController), 'index');
        $router = new Router();

        // Act
        $router->addRoute($route);

        // Assert
        $routes = $router->getRoutes();
        $this->assertEquals(1, count($routes));
        $this->assertEquals($route, $routes[0]);
    }

    /** @test */
    public function can_resolve_route()
    {
        // Arrange
        $homeController = new class() {
            public function index()
            {
                //
            }
        };
        $aboutController = new class() {
            public function index()
            {
                return 'about';
            }
        };
        $homeRoute = new Route('get', '/', get_class($homeController), 'index');
        $aboutRoute = new Route('get', '/about', get_class($aboutController), 'index');
        $router = new Router();
        $router->addRoute($homeRoute);
        $router->addRoute($aboutRoute);
        $serverRequest = $this->getStubServerRequest(['request_target' => '/about', new Uri('/about')]);

        // Act
        $resolvedRoute = $router->resolve($serverRequest);

        // Assert
        $routes = $router->getRoutes();
        $this->assertEquals(2, count($routes));
        $this->assertEquals(get_class($aboutController), $resolvedRoute->getClassName());
        $this->assertEquals('index', $resolvedRoute->getClassMethodName());
    }

    /** @test */
    public function can_resolve_route_with_parameters()
    {
        // Arrange
        $postController = new class() {
            public function show($id)
            {
                return $id;
            }
        };
        $router = new Router();
        $route = new Route('get', '/post/{id}', get_class($postController), 'show');
        $router->addRoute($route);
        $serverRequest = $this->getStubServerRequest(['request_target' => '/post/123', new Uri('/post/123')]);

        // Act
        $resolvedRoute = $router->resolve($serverRequest);

        // Assert
        $routes = $router->getRoutes();
        $this->assertEquals(1, count($routes));
        $this->assertEquals(get_class($postController), $resolvedRoute->getClassName());
        $this->assertEquals('show', $resolvedRoute->getClassMethodName());
        $this->assertEquals([123], $resolvedRoute->getParameters());
    }
}
