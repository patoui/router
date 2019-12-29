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
        $resource = fopen(Stream::TEMPORARY_STREAM, 'rb+');

        if ($resource === false) {
            throw new RuntimeException('Unabled to open temporary resource');
        }

        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
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
        if (! is_resource($resource)) {
            throw new \InvalidArgumentException('Invalid resource, cannot create stream.');
        }

        return new Stream($resource);
    }
}
