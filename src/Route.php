<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class Route implements Routable
{
    /** @var string */
    private string $httpVerb;

    /** @var string */
    private string $path;

    /** @var string */
    private string $className;

    /** @var string */
    private string $classMethodName;

    /** @var array<mixed> */
    private array $parameters;

    public function __construct(
        string $httpVerb,
        string $path,
        string $className,
        string $classMethodName
    ) {
        if (! in_array($httpVerb, ['get', 'post'])) {
            throw new InvalidArgumentException(
                'Invalid http verb, must be: get or post'
            );
        }

        if (! class_exists($className) || ! method_exists($className, $classMethodName)) {
            throw new MethodNotFoundException(
                "Method '{$classMethodName}' not found on class '{$className}'",
                $className,
                $classMethodName
            );
        }

        $this->httpVerb = $httpVerb;
        $this->path = $path;
        $this->className = $className;
        $this->classMethodName = $classMethodName;
        $this->parameters = [];
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    public function getClassMethodName() : string
    {
        return $this->classMethodName;
    }

    public function getHttpVerb() : string
    {
        return $this->httpVerb;
    }

    public function isHttpVerbAndPathAMatch(
        string $httpVerb,
        string $path
    ) : bool {
        $pathParts = explode('/', $path);
        $routePathParts = explode('/', $this->getPath());

        foreach ($routePathParts as $key => $routePathPart) {
            if (isset($pathParts[$key]) && preg_match('/{.+}/', $routePathPart)) {
                $this->parameters[] = $pathParts[$key];
                $routePathParts[$key] = $pathParts[$key];
            }
        }

        $routePath = implode('/', $routePathParts);

        return strcasecmp($this->getHttpVerb(), $httpVerb) === 0 &&
            trim($routePath, '/') === trim($path, '/');
    }

    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * @return array<mixed>
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}
