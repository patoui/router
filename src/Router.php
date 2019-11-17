<?php

declare(strict_types=1);

namespace Patoui\Router;

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
     * @param  string  $httpVerb
     * @param  string  $path
     * @return mixed
     * @throws RouteNotFoundException
     */
    public function resolve(string $httpVerb, string $path)
    {
        /* @var $route Routable */
        foreach ($this->routes as $route) {
            if ($route->isHttpVerbAndPathAMatch($httpVerb, $path)) {
                return $route->resolve();
            }
        }

        throw new RouteNotFoundException(
            "Route path '{$path}' with http verb '{$httpVerb}'"
        );
    }
}
