<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class Route implements Routable
{
    /* @var string */
    private $httpVerb;

    /* @var string */
    private $path;

    /* @var object */
    private $classInstance;

    /* @var string */
    private $classMethodName;

    public function __construct(
        string $httpVerb,
        string $path,
        object $classInstance,
        string $classMethodName
    ) {
        if (! in_array($httpVerb, ['get', 'post'])) {
            throw new InvalidArgumentException(
                'Invalid http verb, must be: get or post'
            );
        }

        if (! method_exists($classInstance, $classMethodName)) {
            throw new MethodNotFoundException(
                "Method '{$classMethodName}' not found on class '{$classInstance}'",
                $classInstance,
                $classMethodName
            );
        }

        $this->httpVerb = $httpVerb;
        $this->path = $path;
        $this->classInstance = $classInstance;
        $this->classMethodName = $classMethodName;
    }

    public function getClassInstance() : object
    {
        return $this->classInstance;
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

    /* @return mixed */
    public function resolve()
    {
        return call_user_func(
            [$this->getClassInstance(), $this->getClassMethodName()]
        );
    }
}
