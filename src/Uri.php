<?php

declare(strict_types=1);

namespace Patoui\Router;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /** @var string */
    private $scheme;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $path;

    /** @var string */
    private $query;

    /** @var string */
    private $fragment;

    public function __construct(string $uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        $host = parse_url($uri, PHP_URL_HOST);
        $port = parse_url($uri, PHP_URL_PORT);
        $port = $port ? (int) $port : 80;
        $user = parse_url($uri, PHP_URL_USER);
        $password = parse_url($uri, PHP_URL_PASS);
        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);

        $this->scheme = $scheme ?: '';
        $this->host = $host ?: '';
        $this->port = $port;
        $this->user = $user ?: '';
        $this->path = $path ?: '';
        $this->password = $password ?: '';
        $this->query = $query ?: '';
        $this->fragment = $fragment ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        return "{$this->user}@{$this->host}" . ($this->port ? ":{$this->port}" : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        if (! $this->user) {
            return '';
        }

        return $this->user . ($this->password ? ":{$this->password}" : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param  string  $scheme  The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        if (preg_match('/^[a-zA-Z0-9+-.]+$/', $scheme) === 0) {
            throw new \InvalidArgumentException(
                "Invalid scheme {$scheme}; please reference RFC3986 for additional details"
            );
        }

        $instance = clone $this;
        $instance->scheme = strtolower($scheme);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $instance = clone $this;
        $instance->user = $user;
        if ($password) {
            $instance->password = $password;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException("Invalid host: {$host}");
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = "[{$host}]";
        }

        $instance = clone $this;
        $instance->host = $host;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        if (!is_int($port)) {
            throw new \InvalidArgumentException("Invalid port: {$port}");
        }

        $instance = clone $this;
        $instance->port = $port;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException("Invalid path: {$path}");
        }

        $instance = clone $this;
        $instance->path = $path;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException("Invalid query: {$query}");
        }

        $instance = clone $this;
        $instance->query = ltrim($query, '?');

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            throw new \InvalidArgumentException("Invalid fragment: {$fragment}");
        }

        $instance = clone $this;
        $instance->fragment = ltrim($fragment, '#');

        return $instance;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }
}
