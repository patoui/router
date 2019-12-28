<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function test_can_resolve_route(): void
    {
        // Arrange
        $routeController = new class() {
            public function index()
            {
                return 'Route Controller';
            }
        };
        $route = new Route('get', '/', get_class($routeController), 'index');

        // Act and Assert
        $this->assertTrue($route->isHttpVerbAndPathAMatch('get', '/'));
    }

    public function test_is_match_with_parameters(): void
    {
        // Arrange
        $routeController = new class() {
            public function show($id)
            {
                return $id;
            }
        };
        $route = new Route('get', '/{id}', get_class($routeController), 'show');

        // Act and Assert
        $this->assertTrue($route->isHttpVerbAndPathAMatch('get', '/123'));
    }
}
