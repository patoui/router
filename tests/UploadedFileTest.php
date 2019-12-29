<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Stream;
use Patoui\Router\UploadedFile;
use Psr\Http\Message\StreamInterface;

class UploadedFileTest extends TestCase
{
    protected function getStubUploadedFile(array $propertyOverrides = []): UploadedFile
    {
        $filePath =__DIR__.DIRECTORY_SEPARATOR.'test.txt';

        if (!isset($propertyOverrides['file'])) {
            file_put_contents($filePath, 'Hello world.');
        } else {
            $filePath = $propertyOverrides['file'];
            unset($propertyOverrides['file']);
        }
        $this->filePaths[] = $filePath;

        $properties = array_merge([
            'file' => $filePath,
        ], $propertyOverrides);

        return new UploadedFile(...array_values($properties));
    }

    public function test_get_stream(): void
    {
        // Arrange
        $filePath =__DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);

        // Act
        $instanceStream = $uploadedFile->getStream();

        // Assert
        $this->assertContains(StreamInterface::class, class_implements($instanceStream));
    }

    public function test_move_to(): void
    {
        // Arrange
        $filePath =__DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;

        // Act
        $uploadedFile->moveTo($pathToMoveTo);

        // Assert
        $this->assertFileExists($pathToMoveTo);
    }
}
