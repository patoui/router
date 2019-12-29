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
}