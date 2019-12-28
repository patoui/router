<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Stream;
use Patoui\Router\UploadedFile;

class UploadedFileTest extends TestCase
{
    protected function getStubUploadedFile(array $propertyOverrides = []): UploadedFile
    {
        $properties = array_merge([
            'stream' => new Stream('Foobar'),
        ], $propertyOverrides);

        return new UploadedFile(...array_values($properties));
    }

    public function test_get_stream(): void
    {
        // Arrange
        $stream = new Stream('Hello World');
        $uploadedFile = $this->getStubUploadedFile([
            'stream' => $stream,
        ]);

        // Act
        $instanceStream = $uploadedFile->getStream();

        // Assert
        $this->assertEquals($stream, $instanceStream);
    }

    public function test_move_to(): void
    {
        // Arrange
        $stream = new Stream('Hello World');
        $uploadedFile = $this->getStubUploadedFile([
            'stream' => $stream,
        ]);
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';

        // Act
        $uploadedFile->moveTo($pathToMoveTo);

        // Assert
        $this->assertFileExists($pathToMoveTo);
    }
}
