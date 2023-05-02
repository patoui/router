<?php

declare(strict_types=1);

namespace Patoui\Router;

interface Routable
{
    public function getClassName(): string;

    public function getClassMethodName(): string;

    public function addParameters(array $parameters): void;

    public function addParameter(string $key, mixed $value): void;

    /** @return array<mixed> */
    public function getParameters(): array;

    public function getHttpVerb(): string;

    public function getPath(): string;
}
