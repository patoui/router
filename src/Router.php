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

        // remove first item if it's an empty string
        if (isset($parts[0]) && $parts[0] === '') {
            unset($parts[0]);
        }

        // add the http verb as the first item in the array
        array_unshift($parts, strtoupper($routable->getHttpVerb()));

        $routes = &$this->routes;

        // build nested assoc array [hash map] from the URL path
        for ($i = 0; $i < count($parts); $i++) {
            $routes[$parts[$i]] = isset($routes[$parts[$i]]) ? $routes[$parts[$i]] : [];
            $routes = &$routes[$parts[$i]];
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

        // remove first item if it's an empty string
        if (isset($parts[0]) && $parts[0] === '') {
            unset($parts[0]);
        }

        // TODO: move to better data structure
        // add the http verb as the first item in the array
        array_unshift($parts, strtoupper($serverRequest->getMethod()));

        $routes = &$this->routes;

        $part = array_shift($parts);
        $params = [];

        while ($part) {
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
                    }
                }
            }

            $routes = &$routes[$part];
            $part = array_shift($parts);
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
