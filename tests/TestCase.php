<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\ServerRequest;
use Patoui\Router\Stream;
use Patoui\Router\Uri;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected function getStubServerRequest(array $propertyOverrides = []) : ServerRequest
    {
        $properties = array_merge([
            'protocol' => '1.1',
            'headers' => ['content-type' => ['application/json']],
            'body' => new Stream('Request Body'),
            'request_target' => '/',
            'method' => 'get',
            'uri' => new Uri('/'),
            'server_params' => [],
            'cookie_params' => [],
            'query_params' => [],
            'uploaded_files' => [],
        ], $propertyOverrides);

        return new ServerRequest(...array_values($properties));
    }
}
