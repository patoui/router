<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /* @var array<Routable> */
    private $routes;

    public function addRoute(Routable $routable) : void
    {
        $this->routes[] = $routable;
    }

    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * @param  ServerRequestInterface $serverRequest
     * @return mixed
     * @throws RouteNotFoundException
     */
    public function resolve(ServerRequestInterface $serverRequest)
    {
        /* @var $route Routable */
        foreach ($this->routes as $route) {
            if ($route->isHttpVerbAndPathAMatch($serverRequest->getMethod(), $serverRequest->getRequestTarget())) {
                return $route->resolve();
            }
        }

        throw new RouteNotFoundException('Route not found');
    }
}
