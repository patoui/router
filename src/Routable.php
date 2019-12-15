<?php

declare(strict_types=1);

namespace Patoui\Router;

interface Routable
{
    public function getClassName() : string;

    public function getClassMethodName() : string;

    public function getHttpVerb() : string;

    public function isHttpVerbAndPathAMatch(string $httpVerb, string $path) : bool;

    public function getPath() : string;

    public function resolve() : array;
}
