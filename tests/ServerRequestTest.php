<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\ServerRequest;
use Patoui\Router\Stream;
use Patoui\Router\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ServerRequestTest extends TestCase
{
    private function getStubServerRequest(array $propertyOverrides = []) : ServerRequest
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
        ], $propertyOverrides);

        return new ServerRequest(...array_values($properties));
    }

    /** @test */
    public function test_get_protocol_version() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $protocolVersion = $serverRequest->getProtocolVersion();

        // Assert
        $this->assertEquals('1.1', $protocolVersion);
    }

    /** @test */
    public function test_with_protocol_version() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'protocol' => '2.0',
            'headers' => ['content-type' => ['text/html']],
        ]);

        // Act
        $serverRequest = $serverRequest->withProtocolVersion('1.1');

        // Assert
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
        $this->assertEquals('text/html', $serverRequest->getHeaderLine('content-type'));
    }

    /** @test */
    public function test_invalid_headers_throws_exception() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getStubServerRequest([
            'headers' => ['content-type' => 'application/json'],
        ]);
    }

    /** @test */
    public function test_get_headers() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['application/json']],
        ]);

        // Act
        $headers = $serverRequest->getHeaders();

        // Assert
        $this->assertEquals(['content-type' => ['application/json']], $headers);
    }

    /** @test */
    public function test_has_header() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['application/json']],
        ]);

        // Act
        $hasHeader = $serverRequest->hasHeader('CONTENT-type');

        // Assert
        $this->assertTrue($hasHeader);
    }

    /** @test */
    public function test_get_header() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['application/json']],
        ]);

        // Act
        $header = $serverRequest->getHeader('content-TYPE');

        // Assert
        $this->assertEquals(['application/json'], $header);
    }

    /** @test */
    public function test_get_header_line() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['text/csv', 'application/json']],
        ]);

        // Act
        $headerLine = $serverRequest->getHeaderLine('content-TYPE');

        // Assert
        $this->assertEquals('text/csv,application/json', $headerLine);
    }

    /** @test */
    public function test_with_header() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['text/csv', 'application/json']],
        ]);

        // Act
        $newServerRequestStatic = $serverRequest->withHeader('content-type', 'text/html');

        // Assert
        $this->assertEquals('text/html', $newServerRequestStatic->getHeaderLine('content-type'));
    }

    /** @test */
    public function test_with_added_header() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => ['content-type' => ['application/json']],
        ]);

        // Act
        $newServerRequestStatic = $serverRequest->withAddedHeader('content-type', 'text/csv');

        // Assert
        $this->assertEquals(
            ['application/json', 'text/csv'],
            $newServerRequestStatic->getHeader('content-type')
        );
    }

    /** @test */
    public function test_without_header() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'headers' => [
                'content-type' => ['application/json'],
                'content-encoding' => ['gzip'],
            ],
        ]);

        // Act
        $newServerRequestStatic = $serverRequest->withoutHeader('content-encoding');

        // Assert
        $this->assertEquals(
            ['content-type' => ['application/json']],
            $newServerRequestStatic->getHeaders()
        );
    }

    /** @test */
    public function test_get_body() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act && Assert
        $this->assertInstanceOf(
            StreamInterface::class,
            $serverRequest->getBody()
        );
    }

    /** @test */
    public function test_with_body() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'body' => new Stream('Request Body'),
        ]);
        $newStream = new Stream('New Body');

        // Act
        $newServerRequestStatic = $serverRequest->withBody($newStream);

        // Assert
        $this->assertEquals($newStream, $newServerRequestStatic->getBody());
    }

    /** @test */
    public function test_get_request_target() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'request_target' => '/blog',
        ]);

        // Act && Assert
        $this->assertEquals('/blog', $serverRequest->getRequestTarget());
    }

    /** @test */
    public function test_with_request_target() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'request_target' => '/blog',
        ]);

        // Act
        $newServerRequestStatic = $serverRequest->withRequestTarget('/post');

        // Assert
        $this->assertEquals('/post', $newServerRequestStatic->getRequestTarget());
    }

    /** @test */
    public function test_get_method() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'method' => 'post',
        ]);

        // Act && Assert
        $this->assertEquals('post', $serverRequest->getMethod());
    }

    /** @test */
    public function test_invalid_method_throws_exception() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getStubServerRequest(['method' => 'foobar']);
    }

    /** @test */
    public function test_with_method() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest(['method' => 'get']);

        // Act
        $newServerRequestStatic = $serverRequest->withMethod('post');

        // Assert
        $this->assertEquals('post', $newServerRequestStatic->getMethod());
    }

    /** @test */
    public function test_get_uri() : void
    {
        // Arrange
        $uri = new Uri('/blog?q=new');
        $serverRequest = $this->getStubServerRequest(['uri' => $uri]);

        // Act && Assert
        $this->assertEquals($uri, $serverRequest->getUri());
    }

    /** @test */
    public function test_with_uri() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();
        $newUri = new Uri('/blog?q=new');

        // Act
        $newServerRequestStatic = $serverRequest->withUri($newUri);

        // Assert
        $this->assertEquals($newUri, $newServerRequestStatic->getUri());
    }

    /** @test */
    public function test_with_uri_with_preserve_host() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'uri' => new Uri('https://otherhost.com/blog'),
        ]);
        $newUri = new Uri('https://example.com/blog?q=new');

        // Act
        $newServerRequestStatic = $serverRequest->withUri($newUri, true);

        // Assert
        $this->assertEquals($newUri, $newServerRequestStatic->getUri());
        $this->assertEquals('otherhost.com', $newServerRequestStatic->getHeaderLine('http_host'));
    }

    /** @test */
    public function test_get_server_params() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'server_params' => ['PATH_INFO' => '/foobar']
        ]);

        // Act && Assert
        $this->assertEquals(['PATH_INFO' => '/foobar'], $serverRequest->getServerParams());
    }

    /** @test */
    public function test_get_cookie_params() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'cookie_params' => ['my_app_user_session' => 'abc123'],
        ]);

        // Act && Assert
        $this->assertEquals(['my_app_user_session' => 'abc123'], $serverRequest->getCookieParams());
    }

    /** @test */
    public function test_with_cookie_params() : void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'cookie_params' => ['foo' => 'bar'],
        ]);

        // Act
        $newServerRequestStatic = $serverRequest->withCookieParams([
            'foo' => 'gibberish',
        ]);

        // Assert
        $this->assertEquals(['foo' => 'gibberish'], $newServerRequestStatic->getCookieParams());
    }
}
