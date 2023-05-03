<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class Route implements Routable
{
    /** @var string[] */
    private const HTTP_VERBS = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'OPTIONS',
        'HEAD',
        'DELETE',
        'CONNECT',
        'TRACE',
    ];

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
        if (! in_array($httpVerb, self::HTTP_VERBS, true)) {
            throw new InvalidArgumentException(
                'Invalid http verb, must be: ' . implode(', ', self::HTTP_VERBS)
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

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClassMethodName(): string
    {
        return $this->classMethodName;
    }

    public function getHttpVerb(): string
    {
        return $this->httpVerb;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function addParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function addParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return array<mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
