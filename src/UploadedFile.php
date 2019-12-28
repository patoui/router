<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

final class UploadedFile implements UploadedFileInterface
{
    private StreamInterface $stream;

    private bool $hasMoved = false;

    private bool $isSapi;

    /**
     * UploadedFile constructor.
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
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
            if (!is_uploaded_file($this->stream)) {
                throw new RuntimeException('Invalid uploaded file');
            }
            if (!move_uploaded_file($this->stream, $targetPath)) {
                throw new RuntimeException('Error occurred while moving file');
            }
        } elseif (!rename($this->stream, $targetPath)) {
            throw new RuntimeException('Error occurred while moving file');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        // TODO: Implement getError() method.
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
