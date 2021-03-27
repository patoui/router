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

    private ?string $name;

    private ?string $type;

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
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * UploadedFile constructor.
     * @param string|StreamInterface $file
     * @param string|null            $name
     * @param string|null            $type
     * @param int|null               $size
     * @param int                    $error
     * @psalm-suppress RedundantConditionGivenDocblockType Used for is_string check.
     */
    public function __construct(
        $file,
        ?string $name = null,
        ?string $type = null,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK
    ) {
        if (! in_array($error, self::$validUploadErrorCodes, true)) {
            throw new InvalidArgumentException('Invalid upload error code.');
        }

        if ($file instanceof StreamInterface) {
            /** @psalm-suppress MixedAssignment */
            $fileUri = $file->getMetadata('uri');
            if (! is_string($fileUri)) {
                throw new InvalidArgumentException('URI not available for given stream');
            }
            $this->file = $fileUri;
            $this->stream = $file;
        } elseif (is_string($file)) {
            $this->file = $file;
            $this->stream = (new StreamFactory())->createStreamFromFile($file);
        } else {
            throw new InvalidArgumentException('Invalid type for file, must be string or implement StreamInterface.');
        }
        $this->size = $size;
        $this->error = $error;
        $this->name = $name;
        $this->type = $type;
        $this->isSapi = ! empty($_FILES);
    }

    /**
     * @return array<UploadedFile>
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArgument
     */
    public static function makeWithGlobals(): array
    {
        $uploadedFiles = [];

        foreach ($_FILES as $file) {
            if (is_string($file['tmp_name']) || (is_object($file['tmp_name']) && $file['tmp_name'] instanceof StreamInterface)) {
                $uploadedFiles[] = new static(
                    $file['tmp_name'],
                    isset($file['name']) ? (string) $file['name'] : null,
                    isset($file['type']) ? (string) $file['type'] : null,
                    isset($file['size']) ? (int) $file['size'] : null,
                    (int) ($file['error'] ?? UPLOAD_ERR_OK),
                );
            }
        }

        return $uploadedFiles;
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
            if (! is_uploaded_file($this->file)) {
                throw new RuntimeException('Invalid uploaded file');
            }
            if (! move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException('Error occurred while moving file');
            }
        } elseif (! rename($this->file, $targetPath)) {
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
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }
}
