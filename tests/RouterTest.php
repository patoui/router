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
        $route = new Route('get', '/', $routeController, 'index');
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
        $route = new Route('get', '/', $routeController, 'index');
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
        $homeRoute = new Route('get', '/', $homeController, 'index');
        $aboutRoute = new Route('get', '/about', $aboutController, 'index');
        $router = new Router();
        $router->addRoute($homeRoute);
        $router->addRoute($aboutRoute);
        $serverRequest = $this->getStubServerRequest(['request_target' => '/about', new Uri('/about')]);

        // Act
        $resolvedRoute = $router->resolve($serverRequest);

        // Assert
        $routes = $router->getRoutes();
        $this->assertEquals(2, count($routes));
        $this->assertEquals('about', $resolvedRoute);
    }
}
