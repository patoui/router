<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

final class UploadedFile implements UploadedFileInterface
{
    private string $file;

    private StreamInterface $stream;

    private bool $hasMoved = false;

    private ?int $size;

    private int $error;

    private bool $isSapi;

    /** @var array<int> */
    private static array $validUploadErrorCodes = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION
    ];

    /**
     * UploadedFile constructor.
     * @param string|StreamInterface $file
     * @param null|int               $size
     * @param int                    $error
     * @throws InvalidArgumentException
     */
    public function __construct(
        $file,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK
    ) {
        if (!in_array($error, self::$validUploadErrorCodes, true)) {
            throw new InvalidArgumentException('Invalid upload error code.');
        }

        if ($file instanceof StreamInterface) {
            /** @psalm-suppress MixedAssignment */
            $fileUri = $file->getMetadata('uri');
            if (!is_string($fileUri)) {
                throw new InvalidArgumentException('URI not available for given stream');
            }
            $this->file = $fileUri;
            $this->stream = $file;
        } elseif (file_exists($file)) {
            $this->file = $file;
            $this->stream = (new StreamFactory())->createStreamFromFile($file);
        } else {
            throw new InvalidArgumentException('Invalid type for file, must be string or implement StreamInterface.');
        }
        $this->size = $size;
        $this->error = $error;
        $this->isSapi = !empty($_FILES);
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath): void
    {
        if ($this->hasMoved) {
            throw new RuntimeException('Uploaded file has already been moved');
        }

        if ($this->isSapi) {
            if (!is_uploaded_file($this->file)) {
                throw new RuntimeException('Invalid uploaded file');
            }
            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException('Error occurred while moving file');
            }
        } elseif (!rename($this->file, $targetPath)) {
            throw new RuntimeException('Error occurred while moving file');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        // TODO: Implement getClientFilename() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        // TODO: Implement getClientMediaType() method.
    }
}
