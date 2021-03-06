<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /** @var array<Routable> */
    private array $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function addRoute(Routable $routable): void
    {
        $this->routes[] = $routable;
    }

    /**
     * @return array<Routable>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param  ServerRequestInterface $serverRequest
     * @return Routable
     * @throws RouteNotFoundException
     */
    public function resolve(ServerRequestInterface $serverRequest): Routable
    {
        foreach ($this->routes as $route) {
            if ($route->isHttpVerbAndPathAMatch($serverRequest->getMethod(), $serverRequest->getRequestTarget())) {
                return $route;
            }
        }

        throw new RouteNotFoundException('Route not found');
    }
}
