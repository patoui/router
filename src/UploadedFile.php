<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadedFile implements UploadedFileInterface
{
    private StreamInterface $stream;

    /**
     * UploadedFile constructor.
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
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
    public function moveTo($targetPath)
    {
        // TODO: Implement moveTo() method.
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
