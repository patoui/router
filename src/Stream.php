<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    public const TEMPORARY_STREAM = 'php://temp';

    /**
     * @var null|resource
     * @psalm-var null|resource|closed-resource
     */
    private $stream;

    /**
     * Stream constructor.
     * @param resource $stream
     */
    public function __construct($stream)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid resource provided.');
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;

        return is_resource($stream) ? $stream : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->stream && is_resource($this->stream)) {
            $stats = fstat($this->stream);
            if (is_array($stats)) {
                /** @var mixed $size */
                $size = $stats['size'] ?? null;
                return $size !== null ? (int) $size : null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if ($this->stream && is_resource($this->stream)) {
            $position = ftell($this->stream);
            if ($position !== false) {
                return $position;
            }
        }

        throw new RuntimeException('Unable to find position of the current position of the file read/write pointer');
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        if ($this->stream && is_resource($this->stream)) {
            return feof($this->stream);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (! $this->isSeekable()) {
            throw new RuntimeException('Unable to seek stream/resource');
        }
        if (
            $this->stream
            && is_resource($this->stream)
            && fseek($this->stream, $offset, $whence) === -1
        ) {
            throw new RuntimeException('Unable to seek stream/resource');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (
            !$this->isSeekable()
            || (
                $this->stream
                && is_resource($this->stream)
                && rewind($this->stream) === false
            )
        ) {
            throw new RuntimeException('Unable to rewind stream/resource');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        if (! $this->stream) {
            return false;
        }

        /** @var mixed $mode */
        $mode = $this->getMetadata('mode');
        $mode = is_scalar($mode) ? (string) $mode : '';

        return strpos($mode, 'w') !== false || strpos($mode, '+') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $bytesWritten = false;

        if ($this->stream && is_resource($this->stream) && $this->isWritable()) {
            $bytesWritten = fwrite($this->stream, $string);
        }

        if ($bytesWritten !== false) {
            return $bytesWritten;
        }

        throw new RuntimeException('Unable to write to stream/resource');
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        if (! $this->stream) {
            return false;
        }

        /** @var mixed $mode */
        $mode = $this->getMetadata('mode');

        if (! is_string($mode)) {
            return false;
        }

        return strpos($mode, 'r') !== false || strpos($mode, '+') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $dataRead = false;

        if ($this->stream && is_resource($this->stream) && $this->isReadable()) {
            $dataRead = fread($this->stream, $length);
        }

        if (is_string($dataRead)) {
            return $dataRead;
        }

        throw new RuntimeException('Unable to read from stream/resource');
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!$this->stream || !is_resource($this->stream)) {
            return '';
        }

        $contents = stream_get_contents($this->stream);

        return $contents === false ? '' : $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (!$this->stream || !is_resource($this->stream)) {
            return null;
        }

        $metadata = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }
}
