<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /** @var array<string, array|Routable> */
    private array $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function addRoute(Routable $routable): void
    {
        $parts = explode('/', $routable->getPath());

        $routes = &$this->routes;

        $routes[$routable->getHttpVerb()] = isset($routes[$routable->getHttpVerb()])
            ? $routes[$routable->getHttpVerb()]
            : [];
        $routes = &$routes[$routable->getHttpVerb()];

        // build nested assoc array [hash map] from the URL path
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $routes[$part] = isset($routes[$part]) ? $routes[$part] : [];
            $routes = &$routes[$part];
        }

        // add routable to the last routes path
        $routes = $routable;
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
        $parts = explode('/', $serverRequest->getRequestTarget());

        $routes = &$this->routes;

        if (!isset($routes[$serverRequest->getMethod()])) {
            throw new RouteNotFoundException('Route not found');
        }

        $routes = &$routes[$serverRequest->getMethod()];
        $params = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (!isset($routes[$part])) {
                foreach (array_keys($routes) as $key) {
                    if (isset($key[0]) && $key[0] === '{') {
                        $paramName = trim($key, '{}');
                        $castToType = null;
                        $paramValue = $part;

                        // check if param has a cast type, e.g. `int|user_id`
                        if (strpos($paramName, '|') !== false) {
                            [$castToType, $paramName] = explode('|', $paramName);
                        }

                        // cast the url segment to the appropriate type
                        if ($castToType) {
                            Type::cast($castToType, $paramValue);
                        }

                        $params[$paramName] = $paramValue;
                        $part = $key;
                    }
                }
            }

            $routes = &$routes[$part];
        }

        if ($routes instanceof Routable) {
            if ($params) {
                $routes->addParameters($params);
            }

            return $routes;
        }

        throw new RouteNotFoundException('Route not found');
    }
}
