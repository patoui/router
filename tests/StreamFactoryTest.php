<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\StreamFactory;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactoryTest extends TestCase
{
    public function test_create_stream(): void
    {
        // Arrange && Act
        $stream = (new StreamFactory())->createStream();

        // Assert
        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function test_create_stream_from_file(): void
    {
        // Arrange && Act
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/Fixtures/test.txt', 'rb+');

        // Assert
        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function test_create_stream_from_file_no_file_throws_exception(): void
    {
        // Arrange
        $this->expectException(RuntimeException::class);

        // Act && Assert
        (new StreamFactory())->createStreamFromFile('foobar', 'rb+');
    }

    public function test_create_stream_from_file_invalid_file_mode_throws_exception(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act && Assert
        (new StreamFactory())->createStreamFromFile('fake_file.txt', 'foobar');
    }

    public function test_create_stream_from_resource(): void
    {
        // Arrange && Act
        $stream = (new StreamFactory())->createStreamFromResource(fopen('php://memory', 'rb+'));

        // Assert
        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function test_create_stream_from_resource_invalid_resource_throws_exception(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act && Assert
        (new StreamFactory())->createStreamFromResource('foobar');
    }
}
