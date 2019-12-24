<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

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
}
