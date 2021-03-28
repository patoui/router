<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
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
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'rb'): StreamInterface
    {
        if (!preg_match('/[rwaxcebt]+\+?/', $mode, $matches) || $matches[0] !== $mode) {
            throw new InvalidArgumentException('Invalid stream mode provided');
        }

        if (
            !file_exists($filename)
            || !is_readable($filename)
            || !$stream = fopen($filename, $mode)
        ) {
            throw new RuntimeException("Unable to open/read file: {$filename}");
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
            throw new InvalidArgumentException('Invalid resource, cannot create stream.');
        }

        return new Stream($resource);
    }
}
