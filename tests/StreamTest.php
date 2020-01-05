<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\Stream;

class StreamTest extends TestCase
{
    /**
     * @param resource $stream stream resource
     * @return Stream
     */
    protected function getStubStream($stream = null): Stream
    {
        if (! $stream) {
            $temporaryStream = fopen('php://memory', 'rb+');
            fwrite($temporaryStream, 'Hello world!');
            rewind($temporaryStream);
            $stream = $temporaryStream;
        }

        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Invalid resource for stream');
        }

        return new Stream($stream);
    }

    public function test_tostring(): void
    {
        // Arrange
        $stream = $this->getStubStream();

        // Act
        $streamString = (string) $stream;

        // Assert
        $this->assertEquals('Hello world!', $streamString);
    }

    public function test_close(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        $this->assertNotEmpty(fstat($resource));

        // Act
        $stream->close();

        // Assert
        $this->assertFalse(is_resource($resource));
    }

    public function test_detach(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        $this->assertTrue(is_resource($resource));
        $this->assertEquals(3, strlen($stream->getContents()));

        // Act
        $detachedResource = $stream->detach();

        // Assert
        $this->assertTrue(is_resource($detachedResource));
        $this->assertEquals(0, strlen($stream->getContents()));
    }

    public function test_get_size(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $streamSize = $stream->getSize();

        // Assert
        $this->assertEquals(3, $streamSize);
    }

    public function test_tell(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        fseek($resource, 2);
        $stream = $this->getStubStream($resource);

        // Act
        $streamTell = $stream->tell();

        // Assert
        $this->assertEquals(2, $streamTell);
    }

    public function test_eof(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $streamEndOfFile = $stream->eof();

        // Assert
        $this->assertFalse($streamEndOfFile);
    }
}
