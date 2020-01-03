<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\ServerRequest;
use Patoui\Router\Stream;
use Patoui\Router\Uri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequestTest extends TestCase
{
    public function test_get_protocol_version(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $protocolVersion = $serverRequest->getProtocolVersion();

        // Assert
        $this->assertEquals('1.1', $protocolVersion);
    }

    public function test_with_protocol_version(): void
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

    public function test_invalid_headers_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getStubServerRequest([
            'headers' => ['content-type' => 'application/json'],
        ]);
    }

    public function test_get_headers(): void
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

    public function test_has_header(): void
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

    public function test_get_header(): void
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

    public function test_get_header_line(): void
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

    public function test_with_header(): void
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

    public function test_with_added_header(): void
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

    public function test_without_header(): void
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

    public function test_get_body(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act && Assert
        $this->assertInstanceOf(
            StreamInterface::class,
            $serverRequest->getBody()
        );
    }

    public function test_with_body(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();
        $temporaryStream = fopen('php://memory', 'rb+');
        fwrite($temporaryStream, 'Another stream');
        rewind($temporaryStream);
        $newStream = new Stream($temporaryStream);

        // Act
        $newServerRequestStatic = $serverRequest->withBody($newStream);

        // Assert
        $this->assertEquals($newStream, $newServerRequestStatic->getBody());
    }

    public function test_get_request_target(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'request_target' => '/blog',
        ]);

        // Act && Assert
        $this->assertEquals('/blog', $serverRequest->getRequestTarget());
    }

    public function test_with_request_target(): void
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

    public function test_get_method(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'method' => 'post',
        ]);

        // Act && Assert
        $this->assertEquals('post', $serverRequest->getMethod());
    }

    public function test_invalid_method_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getStubServerRequest(['method' => 'foobar']);
    }

    public function test_with_method(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest(['method' => 'get']);

        // Act
        $newServerRequestStatic = $serverRequest->withMethod('post');

        // Assert
        $this->assertEquals('post', $newServerRequestStatic->getMethod());
    }

    public function test_get_uri(): void
    {
        // Arrange
        $uri = new Uri('/blog?q=new');
        $serverRequest = $this->getStubServerRequest(['uri' => $uri]);

        // Act && Assert
        $this->assertEquals($uri, $serverRequest->getUri());
    }

    public function test_with_uri(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();
        $newUri = new Uri('/blog?q=new');

        // Act
        $newServerRequestStatic = $serverRequest->withUri($newUri);

        // Assert
        $this->assertEquals($newUri, $newServerRequestStatic->getUri());
    }

    public function test_with_uri_with_preserve_host(): void
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

    public function test_get_server_params(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'server_params' => ['PATH_INFO' => '/foobar'],
        ]);

        // Act && Assert
        $this->assertEquals(['PATH_INFO' => '/foobar'], $serverRequest->getServerParams());
    }

    public function test_get_cookie_params(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'cookie_params' => ['my_app_user_session' => 'abc123'],
        ]);

        // Act && Assert
        $this->assertEquals(['my_app_user_session' => 'abc123'], $serverRequest->getCookieParams());
    }

    public function test_with_cookie_params(): void
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

    public function test_get_query_params(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest([
            'query_params' => ['search' => 'John'],
        ]);

        // Act && Assert
        $this->assertEquals(['search' => 'John'], $serverRequest->getQueryParams());
    }

    public function test_with_query_params(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $newServerRequestStatic = $serverRequest->withQueryParams([
            'search' => 'Doe',
        ]);

        // Assert
        $this->assertEquals(['search' => 'Doe'], $newServerRequestStatic->getQueryParams());
    }

    public function test_get_uploaded_files(): void
    {
        // Arrange
        $uploadedFile = new class implements UploadedFileInterface {
            public function getStream()
            {
                // TODO: Implement getStream() method.
            }

            public function moveTo($targetPath)
            {
                // TODO: Implement moveTo() method.
            }

            public function getSize()
            {
                // TODO: Implement getSize() method.
            }

            public function getError()
            {
                // TODO: Implement getError() method.
            }

            public function getClientFilename()
            {
                // TODO: Implement getClientFilename() method.
            }

            public function getClientMediaType()
            {
                // TODO: Implement getClientMediaType() method.
            }
        };
        $serverRequest = $this->getStubServerRequest([
            'uploaded_files' => [$uploadedFile],
        ]);

        // Act && Assert
        $this->assertEquals([$uploadedFile], $serverRequest->getUploadedFiles());
    }

    public function test_with_uploaded_files(): void
    {
        // Arrange
        $uploadedFile = new class implements UploadedFileInterface {
            public function getStream()
            {
                // TODO: Implement getStream() method.
            }

            public function moveTo($targetPath)
            {
                // TODO: Implement moveTo() method.
            }

            public function getSize()
            {
                // TODO: Implement getSize() method.
            }

            public function getError()
            {
                // TODO: Implement getError() method.
            }

            public function getClientFilename()
            {
                // TODO: Implement getClientFilename() method.
            }

            public function getClientMediaType()
            {
                // TODO: Implement getClientMediaType() method.
            }
        };
        $serverRequest = $this->getStubServerRequest();

        // Act
        $newServerRequestStatic = $serverRequest->withUploadedFiles([
            $uploadedFile,
        ]);

        // Assert
        $this->assertEquals(
            [$uploadedFile],
            $newServerRequestStatic->getUploadedFiles()
        );
    }

    public function test_with_uploaded_files_invalid_type_throws_exception(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);
        $serverRequest = $this->getStubServerRequest();

        // Act && Assert
        $serverRequest->withUploadedFiles(['not an uploaded file']);
    }

    public function test_get_parsed_body(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $parsedBody = $serverRequest->getParsedBody();

        // Assert
        $this->assertTrue(
            is_null($parsedBody) ||
            is_array($parsedBody) ||
            is_object($parsedBody)
        );
    }

    public function test_get_parsed_body_with_content_type_multipart(): void
    {
        // Arrange
        $_POST['foo'] = 'bar';
        $serverRequest = $this->getStubServerRequest([
            'method' => 'post',
            'headers' => ['content-type' => ['multipart/form-data']],
        ]);

        // Act && Assert
        $this->assertEquals(['foo' => 'bar'], $serverRequest->getParsedBody());
    }

    public function test_with_parsed_body(): void
    {
        // Arrange
        $_POST['foo'] = 'bar';
        $serverRequest = $this->getStubServerRequest([
            'method' => 'post',
            'headers' => ['content-type' => ['multipart/form-data']],
        ]);

        // Act
        $newServerRequest = $serverRequest->withParsedBody(['foo' => 'bar']);

        // Assert
        $parsedBody = $newServerRequest->getParsedBody();
        $this->assertEquals(['foo' => 'bar'], $parsedBody);
        $this->assertTrue(
            $parsedBody === null ||
            is_array($parsedBody) ||
            is_object($parsedBody)
        );
    }

    public function test_with_parsed_body_invalid_argument_throws_exception(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();
        $this->expectException(InvalidArgumentException::class);

        // Act
        $serverRequest->withParsedBody(12345);
    }

    public function test_get_attributes(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $attributes = $serverRequest->getAttributes();

        // Assert
        $this->assertEquals([], $attributes);
    }

    public function test_get_attribute(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $attribute = $serverRequest->getAttribute('foo');

        // Assert
        $this->assertEquals(null, $attribute);
    }

    public function test_with_attribute(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();

        // Act
        $newServerRequest = $serverRequest->withAttribute('foo', 'bar');

        // Assert
        $this->assertEquals('bar', $newServerRequest->getAttribute('foo'));
    }

    public function test_without_attribute(): void
    {
        // Arrange
        $serverRequest = $this->getStubServerRequest();
        $withServerRequest = $serverRequest->withAttribute('foo', 'bar');

        // Pre-assert
        $this->assertEquals('bar', $withServerRequest->getAttribute('foo'));

        // Act
        $withoutServerRequest = $withServerRequest->withoutAttribute('foo');

        // Assert
        $this->assertEquals(null, $withoutServerRequest->getAttribute('foo'));
    }

    public function test_make_with_globals(): void
    {
        // Arrange && Act
        $serverRequest = ServerRequest::makeWithGlobals();

        // Assert
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
        $this->assertEquals('/', $serverRequest->getRequestTarget());
        $this->assertEquals([], $serverRequest->getHeaders());
    }
}
