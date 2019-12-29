<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\ServerRequest;
use Patoui\Router\Stream;
use Patoui\Router\Uri;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\StreamInterface;

class TestCase extends PHPUnitTestCase
{
    /**
     * @var array<string> File paths to clean up after tests have run
     */
    protected array $filePaths = [];

    public function tearDown(): void
    {
        foreach ($this->filePaths as $filePath) {
            @unlink($filePath);
        }
    }

    protected function getStubServerRequest(array $propertyOverrides = []) : ServerRequest
    {
        if (! isset($propertyOverrides['body'])) {
            $temporaryStream = fopen('php://memory', 'rb+');
            fwrite($temporaryStream, 'Hello world!');
            rewind($temporaryStream);
            $propertyOverrides['body'] = new Stream($temporaryStream);
        } elseif (!$propertyOverrides['body'] instanceof StreamInterface) {
            throw new InvalidArgumentException('Body must be an instance of: '.StreamInterface::class);
        }

        $properties = array_merge([
            'protocol' => '1.1',
            'headers' => ['content-type' => ['application/json']],
            'body' => null,
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
