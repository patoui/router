<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class Route implements Routable
{
    /** @var string */
    private $httpVerb;

    /** @var string */
    private $path;

    /** @var string */
    private $className;

    /** @var string */
    private $classMethodName;

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
        return strtolower($this->getHttpVerb()) === strtolower($httpVerb) &&
            trim($this->getPath(), '/') === trim($path, '/');
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function resolve() : array
    {
        return [$this->getClassName(), $this->getClassMethodName()];
    }
}
