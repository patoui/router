<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\ServerRequest;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    /** @test */
    public function test_get_protocol_version() : void
    {
        // Arrange
        $serverRequest = new ServerRequest();
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // Act
        $protocolVersion = $serverRequest->getProtocolVersion();

        // Assert
        $this->assertEquals('1.1', $protocolVersion);
    }

    /** @test */
    public function test_with_protocol_version() : void
    {
        // Arrange & Act
        $serverRequest = (new ServerRequest)->withProtocolVersion('1.1');

        // Assert
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
    }

    /** @test */
    public function test_invalid_headers_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ServerRequest('1.1', ['content-type' => 'application/json']);
    }

    /** @test */
    public function test_get_headers() : void
    {
        // Arrange
        $serverRequest = new ServerRequest('1.1', ['content-type' => ['application/json']]);

        // Act
        $headers = $serverRequest->getHeaders();

        // Assert
        $this->assertEquals(['content-type' => ['application/json']], $headers);
    }

    /** @test */
    public function test_has_header() : void
    {
        // Arrange
        $serverRequest = new ServerRequest('1.1', ['content-type' => ['application/json']]);

        // Act
        $hasHeader = $serverRequest->hasHeader('CONTENT-type');

        // Assert
        $this->assertTrue($hasHeader);
    }

    public function test_get_header() : void
    {
        // Arrange
        $serverRequest = new ServerRequest('1.1', ['content-type' => ['application/json']]);

        // Arrange & Act
        $header = $serverRequest->getHeader('content-TYPE');

        // Assert
        $this->assertEquals(['application/json'], $header);
    }
}
