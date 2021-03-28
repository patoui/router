<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\Stream;
use RuntimeException;

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
        self::assertEquals('Hello world!', $streamString);
    }

    public function test_close(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        self::assertNotEmpty(fstat($resource));

        // Act
        $stream->close();

        // Assert
        self::assertFalse(is_resource($resource));
    }

    public function test_detach(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        self::assertIsResource($resource);
        self::assertEquals(3, strlen($stream->getContents()));

        // Act
        $detachedResource = $stream->detach();

        // Assert
        self::assertIsResource($detachedResource);
        self::assertEquals(0, strlen($stream->getContents()));
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
        self::assertEquals(3, $streamSize);
    }

    public function test_get_size_closed(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);
        $stream->detach();

        // Act
        $streamSize = $stream->getSize();

        // Assert
        self::assertEquals(null, $streamSize);
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
        self::assertEquals(2, $streamTell);
    }

    public function test_tell_no_stream_throw_exception(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        fseek($resource, 2);
        $stream = $this->getStubStream($resource);
        $stream->detach();
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $stream->tell();
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
        self::assertFalse($streamEndOfFile);
    }

    public function test_eof_no_stream_true(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);
        $stream->detach();

        // Act && Assert
        self::assertTrue($stream->eof());
    }

    public function test_isseekable(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $isSeekable = $stream->isSeekable();

        // Assert
        self::assertTrue($isSeekable);
    }

    public function test_isseekable_no_stream(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);
        $stream->detach();

        // Act && Assert
        self::assertFalse($stream->isSeekable());
    }

    public function test_isseekable_no_metadata_seekable(): void
    {
        // Arrange
        $resource = fopen('php://output', 'wb');
        $stream = $this->getStubStream($resource);

        // Act && Assert
        self::assertFalse($stream->isSeekable());
    }

    public function test_seek(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        self::assertEquals(0, ftell($resource));

        // Act
        $stream->seek(2);

        // Assert
        self::assertEquals(2, ftell($resource));
    }

    public function test_seek_not_seekable_throws_exception(): void
    {
        // Arrange
        $resource = fopen('php://output', 'wb');
        $stream = $this->getStubStream($resource);
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $stream->seek(111);
    }

    public function test_seek_fail_throw_exception(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Pre-assert
        self::assertEquals(0, ftell($resource));
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $stream->seek(11111);
    }

    public function test_rewind(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        fseek($resource, 2);
        $stream = $this->getStubStream($resource);

        // Act
        $stream->rewind();

        // Assert
        self::assertEquals(0, ftell($resource));
    }

    public function test_rewind_not_seekable_throws_exception(): void
    {
        // Arrange
        $resource = fopen('php://output', 'wb');
        $stream = $this->getStubStream($resource);
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $stream->rewind();
    }

    public function test_iswritable(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $isWritable = $stream->isWritable();

        // Assert
        self::assertTrue($isWritable);
    }

    public function test_iswritable_no_stream(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);
        $stream->detach();

        // Act && Assert
        self::assertFalse($stream->isWritable());
    }

    public function test_iswritable_no_writable(): void
    {
        // Arrange
        $resource = fopen('php://input', 'rb');
        $stream = $this->getStubStream($resource);

        // Act && Assert
        self::assertFalse($stream->isWritable());
    }

    public function test_write(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $bytesWritten = $stream->write('Foobar');

        // Assert
        self::assertEquals(6, $bytesWritten);
    }

    public function test_write_not_writeable(): void
    {
        // Arrange
        $resource = fopen('php://input', 'rb');
        $stream = $this->getStubStream($resource);
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $stream->write('Foobar');
    }

    public function test_isreadable(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foo');
        $stream = $this->getStubStream($resource);

        // Act
        $isWritable = $stream->isReadable();

        // Assert
        self::assertTrue($isWritable);
    }

    public function test_read(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foobar');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $readData = $stream->read(3);

        // Assert
        self::assertEquals('Foo', $readData);
    }

    public function test_get_contents(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foobar');
        fseek($resource, 3);
        $stream = $this->getStubStream($resource);

        // Act
        $contents = $stream->getContents();

        // Assert
        self::assertEquals('bar', $contents);
    }

    public function test_get_metadata(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, 'Foobar');
        rewind($resource);
        $stream = $this->getStubStream($resource);

        // Act
        $metadata = $stream->getMetadata();

        // Assert
        self::assertTrue(is_array($metadata));
        self::assertEquals('php://memory', $metadata['uri']);
    }
}
