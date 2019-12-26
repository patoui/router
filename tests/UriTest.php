<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use InvalidArgumentException;
use Patoui\Router\Uri;

class UriTest extends TestCase
{
    protected function getStubUri(?string $uriOverride = null) : Uri
    {
        $uri = $uriOverride ?: 'https://example.com:8080/foo/bar?q=php';
        return new Uri($uri);
    }

    /** @test */
    public function test_get_scheme(): void
    {
        // Arrange
        $uri = $this->getStubUri();

        // Act
        $scheme = $uri->getScheme();

        // Assert
        $this->assertEquals('https', $scheme);
    }

    /** @test */
    public function test_get_authority(): void
    {
        // Arrange
        $uri = $this->getStubUri('root@example.com:8888/foo/bar?q=blah');

        // Act
        $authority = $uri->getAuthority();

        // Assert
        $this->assertEquals('root@example.com:8888', $authority);
    }

    /** @test */
    public function test_get_user_info(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://root:some_password@example.com:8888/foo/bar?q=blah');

        // Act
        $userInfo = $uri->getUserInfo();

        // Assert
        $this->assertEquals('root:some_password', $userInfo);
    }

    /** @test */
    public function test_get_user_info_empty_when_not_present(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://example.com:8888/foo/bar?q=blah');

        // Act
        $userInfo = $uri->getUserInfo();

        // Assert
        $this->assertEquals('', $userInfo);
    }

    /** @test */
    public function test_get_host(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://example.com:8888/foo/bar?q=blah');

        // Act
        $host = $uri->getHost();

        // Assert
        $this->assertEquals('example.com', $host);
    }

    /** @test */
    public function test_get_port(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://example.com:8888/foo/bar?q=blah');

        // Act
        $port = $uri->getPort();

        // Assert
        $this->assertEquals(8888, $port);
    }

    /** @test */
    public function test_get_path(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://root:some_password@example.com:8888/foo/bar?q=blah');

        // Act
        $path = $uri->getPath();

        // Assert
        $this->assertEquals('/foo/bar', $path);
    }

    /** @test */
    public function test_get_query(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://root:some_password@example.com:8888/foo/bar?q=blah');

        // Act
        $query = $uri->getQuery();

        // Assert
        $this->assertEquals('q=blah', $query);
    }

    /** @test */
    public function test_get_fragment(): void
    {
        // Arrange
        $uri = $this->getStubUri('https://root:some_password@example.com:8888/foo/bar?q=blah#section_1');

        // Act
        $fragment = $uri->getFragment();

        // Assert
        $this->assertEquals('section_1', $fragment);
    }

    /** @test */
    public function test_with_scheme(): void
    {
        // Arrange
        $uri = $this->getStubUri('ftp://example.com');

        // Pre-assert
        $this->assertEquals('ftp', $uri->getScheme());

        // Act
        $newUri = $uri->withScheme('https');

        // Assert
        $this->assertEquals('https', $newUri->getScheme());
    }

    /** @test */
    public function test_with_scheme_throws_exception_when_invalid(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withScheme('$@#%^$#^$#&*^%');
    }

    /** @test */
    public function test_with_user_info(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');

        // Act
        $newUri = $uri->withUserInfo('admin', 'password');

        // Assert
        $this->assertEquals('admin:password', $newUri->getUserInfo());
    }

    /** @test */
    public function test_with_host(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');

        // Act
        $newUri = $uri->withHost('192.168.1.1');

        // Assert
        $this->assertEquals('192.168.1.1', $newUri->getHost());
    }

    /** @test */
    public function test_with_host_ipv6(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');

        // Act
        $newUri = $uri->withHost('::1');

        // Assert
        $this->assertEquals('[::1]', $newUri->getHost());
    }

    /** @test */
    public function test_with_host_invalid_value_throws_exception(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withHost(111);
    }

    /** @test */
    public function test_with_port(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com:8888');

        // Act
        $newUri = $uri->withPort(9999);

        // Assert
        $this->assertEquals(9999, $newUri->getPort());
    }

    /** @test */
    public function test_with_port_invalid_value_throws_exception(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withPort('foobar');
    }

    /** @test */
    public function test_with_path(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com/baz');

        // Act
        $newUri = $uri->withPath('foo/bar');

        // Assert
        $this->assertEquals('foo/bar', $newUri->getPath());
    }

    /** @test */
    public function test_with_path_invalid_value_throws_exception(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withPath(9999);
    }

    /** @test */
    public function test_with_query(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com/?q=php');

        // Act
        $newUri = $uri->withQuery('?foo=bar');

        // Assert
        $this->assertEquals('foo=bar', $newUri->getQuery());
    }

    /** @test */
    public function test_with_query_invalid_value_throws_exception(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withQuery(9999);
    }

    /** @test */
    public function test_with_fragment(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com/#section_1');

        // Act
        $newUri = $uri->withFragment('#section_99');

        // Assert
        $this->assertEquals('section_99', $newUri->getFragment());
    }

    /** @test */
    public function test_with_fragment_invalid_value_throws_exception(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com');
        $this->expectException(InvalidArgumentException::class);

        // Act
        $uri->withQuery(9999);
    }

    /** @test */
    public function test_tostring(): void
    {
        // Arrange
        $uri = $this->getStubUri('http://example.com:8888/foo/bar?q=php#section_1');

        // Act & Assert
        $this->assertEquals('http://example.com:8888/foo/bar?q=php#section_1', $uri->__toString());
    }
}
