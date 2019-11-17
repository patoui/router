<?php

declare(strict_types=1);

namespace Patoui\Router;

interface Routable
{
    public function getClassInstance() : object;

    public function getClassMethodName() : string;

    public function getHttpVerb() : string;

    public function isHttpVerbAndPathAMatch(string $httpVerb, string $path) : bool;

    public function getPath() : string;

    /* @return mixed */
    public function resolve();
}
