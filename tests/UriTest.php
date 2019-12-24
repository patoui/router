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
}
