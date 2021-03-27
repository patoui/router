<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\Route;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class RouteTest extends TestCase
{
    public function test_invalid_http_verb_throws_exception(): void
    {
        // Arrange
        $routeController = new class() {
            public function index()
            {
                return 'Route Controller';
            }
        };
        $this->expectException(InvalidArgumentException::class);

        // Act && Assert
        new Route('foobar', '/', get_class($routeController), 'index');
    }
    public function test_invalid_class(): void
    {
        // Arrange
        $this->expectException(MethodNotFoundException::class);

        // Act && Assert
        new Route('get', '/', 'FoobarClass', 'index');
    }

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
        self::assertTrue($route->isHttpVerbAndPathAMatch('get', '/'));
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
        self::assertTrue($route->isHttpVerbAndPathAMatch('get', '/123'));
    }
}
