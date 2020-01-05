<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    public const TEMPORARY_STREAM = 'php://temp';

    /** @var null|resource */
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
        if ($stream !== null && ! is_resource($stream)) {
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

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->stream) {
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
        if ($this->stream) {
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
        return $this->stream ? feof($this->stream) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        if (!$this->stream) {
            return false;
        }

        /** @var mixed $seekable */
        $seekable = $this->getMetadata('seekable');

        if (! is_bool($seekable)) {
            return false;
        }

        return $seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (! $this->isSeekable() || ($this->stream && fseek($this->stream, $offset, $whence) === -1)) {
            throw new RuntimeException('Unable to seek stream/resource');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (! $this->isSeekable() || ($this->stream && rewind($this->stream) === false)) {
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

        if (! is_string($mode)) {
            return false;
        }

        return strpos($mode, 'w') !== false || strpos($mode, '+') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $bytesWritten = false;

        if ($this->stream && $this->isWritable()) {
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

        if ($this->stream && $this->isReadable()) {
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
        if (! $this->stream) {
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
        if (! $this->stream) {
            return null;
        }

        $metadata = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }
}
