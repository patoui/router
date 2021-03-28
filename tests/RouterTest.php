<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Route;
use Patoui\Router\RouteNotFoundException;
use Patoui\Router\Router;
use Patoui\Router\Uri;

class RouterTest extends TestCase
{
    public function test_can_get_routes(): void
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
        self::assertEquals(1, count($routes));
        self::assertEquals($route, $routes[0]);
    }

    public function test_can_add_route(): void
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
        self::assertEquals(1, count($routes));
        self::assertEquals($route, $routes[0]);
    }

    public function test_can_resolve_route(): void
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
        self::assertEquals(2, count($routes));
        self::assertEquals(get_class($aboutController), $resolvedRoute->getClassName());
        self::assertEquals('index', $resolvedRoute->getClassMethodName());
    }

    public function test_can_resolve_route_with_parameters(): void
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
        self::assertCount(1, $routes);
        self::assertEquals(get_class($postController), $resolvedRoute->getClassName());
        self::assertEquals('show', $resolvedRoute->getClassMethodName());
        self::assertEquals(['id' => '123'], $resolvedRoute->getParameters());
    }

    public function test_can_resolve_route_with_casted_parameters(): void
    {
        // Arrange
        $postController = new class() {
            public function show($id)
            {
                return $id;
            }
        };
        $router = new Router();
        $route = new Route('get', '/post/{int|id}', get_class($postController), 'show');
        $router->addRoute($route);
        $serverRequest = $this->getStubServerRequest(['request_target' => '/post/123', new Uri('/post/123')]);

        // Act
        $resolvedRoute = $router->resolve($serverRequest);

        // Assert
        $routes = $router->getRoutes();
        self::assertCount(1, $routes);
        self::assertEquals(get_class($postController), $resolvedRoute->getClassName());
        self::assertEquals('show', $resolvedRoute->getClassMethodName());
        self::assertSame(123, $resolvedRoute->getParameters()['id']);
    }

    public function test_resolve_route_not_found(): void
    {
        // Arrange
        $router = new Router();
        $serverRequest = $this->getStubServerRequest(['request_target' => '/post/123', new Uri('/post/123')]);
        $this->expectException(RouteNotFoundException::class);

        // Act && Assert
        $router->resolve($serverRequest);
    }
}
