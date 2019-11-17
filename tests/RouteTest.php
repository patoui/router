<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    /** @test */
    public function can_resolve_route()
    {
        // Arrange
        $routeController = new class () {
            public function index() {
                return 'Route Controller';
            }
        };
        $route = new Route('get', '/', $routeController, 'index');

        // Act and Assert
        $this->assertEquals('Route Controller', $route->resolve());
    }
}
