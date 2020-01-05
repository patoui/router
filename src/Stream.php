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
                return $stats['size'] ?? null;
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

        $seekable = $this->getMetadata('seekable');

        return $seekable !== null ? (bool) $seekable : false;
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
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        // TODO: Implement isWritable() method.
    }

    /**
     * Write data to the stream.
     *
     * @param  string  $string  The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws RuntimeException on failure.
     */
    public function write($string)
    {
        // TODO: Implement write() method.
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
    }

    /**
     * Read data from the stream.
     *
     * @param  int  $length  Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws RuntimeException if an error occurs.
     */
    public function read($length)
    {
        // TODO: Implement read() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->stream ?
            stream_get_contents($this->stream) :
            '';
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param  string  $key  Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (! $this->stream) {
            return null;
        }

        $metadata = stream_get_meta_data($this->stream);

        return $metadata[$key] ?? null;
    }
}
