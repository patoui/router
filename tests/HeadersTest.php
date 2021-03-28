<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Headers;

class HeadersTest extends TestCase
{
    public function test_get_headers_from_globals(): void
    {
        // Arrange
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        // Act
        $headers = Headers::getHeadersArrayFromGlobals();

        // Assert
        self::assertCount(1, $headers);
        self::assertEquals('application/json', $headers['ACCEPT'][0]);
    }
}
