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

    /** @test */
    public function is_match_with_parameters()
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
