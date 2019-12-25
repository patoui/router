<?php

declare(strict_types=1);

namespace Patoui\Router;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest implements ServerRequestInterface
{
    /**
     * @var string Represent the HTTP version number (e.g., "1.1", "1.0")
     */
    private $version;

    /**
     * @var array|array<array-key, array<array-key, string>> Contains header by key and array.
     * e.g. ['content-type' => ['application/json']]
     */
    private $headers;

    /** @var StreamInterface */
    private $body;

    /** @var string */
    private $requestTarget;

    /** @var string */
    private $method;

    /** @var UriInterface */
    private $uri;

    /** @var array<mixed> */
    private $serverParams;

    /** @var array<mixed> */
    private $cookieParams;

    /** @var array<mixed> */
    private $queryParams;

    /** @var array<UploadedFileInterface> */
    private $uploadedFiles;

    /** @var null|array<mixed>|object */
    private $parsedBody;

    /** @var array<mixed> */
    private $attributes;

    public function __construct(
        string $version,
        array $headers,
        StreamInterface $body,
        string $requestTarget,
        string $method,
        UriInterface $uri,
        array $serverParams,
        array $cookieParams,
        array $queryParams,
        array $uploadedFiles
    ) {
        $this->validateProtocolVersion($version);
        $this->validateHeaders($headers);
        $this->validateMethod($method);

        $this->version = $version;
        $this->headers = $headers;
        $this->body = $body;
        $this->requestTarget = $requestTarget;
        $this->method = $method;
        $this->uri = $uri;
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->attributes = [];
    }

    /**
     * Create instance of server request based on global values.
     * @return static
     */
    public static function makeWithGlobals(): self
    {
        $headers = Headers::getHeadersArrayFromGlobals();
        $protocolVersion = strval($_SERVER['SERVER_PROTOCOL'] ?? '1.1');
        $protocolVersion = str_replace('HTTP/', '', $protocolVersion);
        $requestTarget = strval($_SERVER['REQUEST_URI'] ?? '/');
        $method = strval($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // TODO: identify potential risk of using globals
        return new static (
            $protocolVersion,
            $headers,
            new Stream('body'),
            $requestTarget,
            $method,
            new Uri($requestTarget),
            $_SERVER,
            $_COOKIE,
            $_GET,
            $_FILES
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version) : self
    {
        $this->validateProtocolVersion($version);

        $instance = clone $this;
        $instance->version = $version;

        return $instance;
    }

    /**
     * Verifies the protocol version.
     *
     * @throws InvalidArgumentException
     * @param  string  $version The version string MUST contain only the HTTP
     * version number (e.g., "1.1", "1.0").
     */
    private function validateProtocolVersion(string $version) : void
    {
        if (! in_array($version, ['1.1', '2.0'])) {
            throw new InvalidArgumentException("Invalid HTTP version: {$version}");
        }
    }

    /**
     * @psalm-suppress MixedReturnTypeCoercion
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return array_key_exists(
            mb_strtoupper($name),
            array_change_key_case($this->headers, CASE_UPPER)
        );
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        $name = mb_strtoupper($name);
        $headers = array_change_key_case($this->headers, CASE_UPPER);

        if (array_key_exists($name, $headers) === false) {
            return [];
        }

        return $headers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $newHeaders = array_merge($this->getHeaders(), [$name => [$value]]);

        $instance = clone $this;
        $instance->headers = $newHeaders;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $newHeaders = $this->getHeaders();
        $headerToUpdate = $this->getHeader($name);
        $headerToUpdate[] = $value;
        $newHeaders[$name] = $headerToUpdate;

        $instance = clone $this;
        $instance->headers = $newHeaders;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $newHeaders = $this->getHeaders();
        unset($newHeaders[$name]);

        $instance = clone $this;
        $instance->headers = $newHeaders;

        return $instance;
    }

    /**
     * Verifies the headers are valid.
     *
     * @throws InvalidArgumentException
     * @param  array  $headers Headers for the incoming request
     */
    private function validateHeaders(array $headers) : void
    {
        $exceptionMessage = 'Invalid headers: '.json_encode($headers);

        if (empty($headers)) {
            return;
        }

        $headersWithArraysOnly = array_filter($headers, function ($header) {
            return is_array($header);
        });

        if (count($headers) !== count($headersWithArraysOnly)) {
            throw new InvalidArgumentException($exceptionMessage);
        }

        foreach ($headers as $key => $header) {
            $headerWithStringValuesOnly = array_filter($header, function ($headerValue) {
                return is_string($headerValue);
            });

            if (count($header) !== count($headerWithStringValuesOnly)) {
                throw new InvalidArgumentException($exceptionMessage);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $instance = clone $this;
        $instance->body = $body;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $instance = clone $this;
        $instance->requestTarget = (string) $requestTarget;

        return $instance;
    }

    /**
     * Verifies the HTTP method is valid.
     *
     * @throws InvalidArgumentException
     * @param  string  $method HTTP method for the incoming request
     */
    private function validateMethod(string $method) : void
    {
        if (! in_array(strtoupper($method), ['POST', 'GET', 'OPTIONS'])) {
            throw new InvalidArgumentException("Invalid HTTP method: {$method}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);

        $instance = clone $this;
        $instance->method = $method;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $headers = $this->getHeaders();
        $currentUriHost = $this->uri->getHost();

        if ($preserveHost && $currentUriHost) {
            $headers['HTTP_HOST'] = [$currentUriHost];
        }

        $instance = clone $this;
        $instance->headers = $headers;
        $instance->uri = $uri;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $instance = clone $this;
        $instance->cookieParams = $cookies;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $instance = clone $this;
        $instance->queryParams = array_merge($this->getQueryParams(), $query);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $filteredUploadedFiles = array_filter($uploadedFiles, function ($uploadedFile) {
            return $uploadedFile instanceof UploadedFileInterface;
        });

        if (count($filteredUploadedFiles) !== count($uploadedFiles)) {
            throw new InvalidArgumentException(
                'Must be an array with instances of '
                .UploadedFileInterface::class
            );
        }

        $instance = clone $this;
        $instance->uploadedFiles = $uploadedFiles;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        $isPost = $this->isPostRequest();

        if ($isPost) {
            return $_POST;
        }

        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (! is_null($data) && ! is_object($data) && ! is_array($data)) {
            throw new InvalidArgumentException(
                'Parsed body must be of type: null, array, or object'
            );
        }

        $data = (array) $data;
        $isPost = $this->isPostRequest();
        $instance = clone $this;
        $instance->parsedBody = array_merge((array) $this->getParsedBody(), $data);

        if ($isPost) {
            // TODO: identify potential risk with assigning values to the super global variable
            $_POST = array_merge($_POST, $data);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $instance = clone $this;
        $instance->attributes[$name] = $value;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $instance = clone $this;

        unset($instance->attributes[$name]);

        return $instance;
    }

    /**
     * Determines if the request is a POST request based on content type headers.
     * @return bool
     */
    private function isPostRequest() : bool
    {
        foreach ($this->getHeader('content-type') as $contentType) {
            if ($contentType === 'application/x-www-form-urlencoded' ||
                $contentType === 'multipart/form-data') {
                return true;
            }
        }

        return false;
    }
}
