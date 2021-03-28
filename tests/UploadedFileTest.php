<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\StreamFactory;
use Patoui\Router\UploadedFile;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class UploadedFileTest extends TestCase
{
    protected function getStubUploadedFile(array $propertyOverrides = []): UploadedFile
    {
        $filePath = __DIR__.DIRECTORY_SEPARATOR.'test.txt';

        if (! isset($propertyOverrides['file'])) {
            file_put_contents($filePath, 'Hello world.');
        } else {
            $filePath = $propertyOverrides['file'];
            unset($propertyOverrides['file']);
        }
        $this->filePaths[] = $filePath;

        $properties = array_merge([
            'file' => $filePath,
            'name' => uniqid('', true).'.txt',
            'type' => 'text/plain',
            'size' => random_int(1, 100),
            'error' => UPLOAD_ERR_OK,
        ], $propertyOverrides);

        return new UploadedFile(...array_values($properties));
    }

    public function test_construct_with_stream_interface(): void
    {
        // Arrange
        $stream = (new StreamFactory())->createStreamFromResource(fopen('php://temp', 'rb'));
        $this->expectNotToPerformAssertions();

        // Act && Assert
        new UploadedFile($stream);
    }

    public function test_construct_with_invalid_type(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act && Assert
        new UploadedFile(12345);
    }

    public function test_make_with_globals(): void
    {
        // Arrange
        $file = tmpfile();
        fwrite($file, 'foobar');
        rewind($file);
        $meta = stream_get_meta_data($file);
        $this->filePaths[] = $meta['uri'];
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;
        $_FILES['upload'] = [
            'name'     => str_replace('/tmp/', '', $meta['uri']),
            'type'     => 'text/plain',
            'tmp_name' => $meta['uri'],
            'error'    => 0,
            'size'     => filesize($meta['uri']),
        ];

        // Act
        $uploadedFiles = UploadedFile::makeWithGlobals();

        // Assert
        self::assertCount(1, $uploadedFiles);
    }

    public function test_get_stream(): void
    {
        // Arrange
        $filePath = __DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);

        // Act
        $instanceStream = $uploadedFile->getStream();

        // Assert
        self::assertContains(StreamInterface::class, class_implements($instanceStream));
    }

    public function test_get_stream_already_moved_raise_exception(): void
    {
        // Arrange
        $filePath = __DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $uploadedFile->moveTo($pathToMoveTo);
        $uploadedFile->getStream();
    }

    public function test_move_to(): void
    {
        // Arrange
        $filePath = __DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;

        // Act
        $uploadedFile->moveTo($pathToMoveTo);

        // Assert
        self::assertFileExists($pathToMoveTo);
    }

    public function test_move_to_already_moved_raise_exception(): void
    {
        // Arrange
        $filePath = __DIR__.DIRECTORY_SEPARATOR.'test.txt';
        $this->filePaths[] = $filePath;
        file_put_contents($filePath, 'Hello world.');
        $uploadedFile = $this->getStubUploadedFile(['file' => $filePath]);
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;
        $this->expectException(RuntimeException::class);

        // Act && Assert
        $uploadedFile->moveTo($pathToMoveTo);
        $uploadedFile->moveTo($pathToMoveTo);
    }

    public function test_move_to_sapi(): void
    {
        self::markTestSkipped('Determine how to test file upload and move via sapi');
        // Arrange
        $file = tmpfile();
        fwrite($file, 'foobar');
        rewind($file);
        $meta = stream_get_meta_data($file);
        $this->filePaths[] = $meta['uri'];
        $pathToMoveTo = __DIR__.DIRECTORY_SEPARATOR.'test-file.txt';
        $this->filePaths[] = $pathToMoveTo;
        $_FILES['upload'] = [
            'name'     => str_replace('/tmp/', '', $meta['uri']),
            'type'     => 'text/plain',
            'tmp_name' => $meta['uri'],
            'error'    => 0,
            'size'     => filesize($meta['uri']),
        ];
        $uploadedFile = UploadedFile::makeWithGlobals()[0];

        // Act
        $uploadedFile->moveTo($pathToMoveTo);

        // Assert
        self::assertFileExists($pathToMoveTo);
    }

    public function test_get_size(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile(['size' => 99999]);

        // Act
        $size = $uploadedFile->getSize();

        // Assert
        self::assertEquals(99999, $size);
    }

    public function test_get_error(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile();

        // Act
        $error = $uploadedFile->getError();

        // Assert
        self::assertEquals(UPLOAD_ERR_OK, $error);
    }

    public function test_setting_invalid_error_code_throws_exception(): void
    {
        // Arrange & Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->getStubUploadedFile(['error' => 999]);
    }

    public function test_get_client_filename(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile(['name' => 'my-file.txt']);

        // Act
        $fileName = $uploadedFile->getClientFilename();

        // Assert
        self::assertEquals('my-file.txt', $fileName);
    }

    public function test_get_client_media_type(): void
    {
        // Arrange
        $uploadedFile = $this->getStubUploadedFile([
            'name' => 'index.html',
            'type' => 'text/html',
        ]);

        // Act
        $mediaType = $uploadedFile->getClientMediaType();

        // Assert
        self::assertEquals('text/html', $mediaType);
    }
}
