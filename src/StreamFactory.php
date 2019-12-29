<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = fopen(Stream::TEMPORARY_STREAM, 'rb+');
        fwrite($stream, $content);
        rewind($stream);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'rb'): StreamInterface
    {
        $stream = fopen($filename, $mode);

        if ($stream === false) {
            throw new RuntimeException("Unable to open file: {$filename}");
        }

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Invalid resource, cannot create stream.');
        }

        return new Stream($resource);
    }
}
