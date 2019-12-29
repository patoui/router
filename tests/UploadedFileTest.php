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
            'size' => random_int(1, 100),
            'error' => UPLOAD_ERR_OK,
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

    public function test_get_size(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile(['size' => 99999]);

        // Act
        $size = $uploadedFile->getSize();

        // Assert
        $this->assertEquals(99999, $size);
    }

    public function test_get_error(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile();

        // Act
        $error = $uploadedFile->getError();

        // Assert
        $this->assertEquals(UPLOAD_ERR_OK, $error);
    }

    public function test_setting_invalid_error_code_throws_exception(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->getStubUploadedFile(['error' => 999]);
    }
}
