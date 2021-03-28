<?php

declare(strict_types=1);

namespace Patoui\Router\Tests;

use Patoui\Router\Type;

class TypeTest extends TestCase
{
    public function test_integer(): void
    {
        self::assertSame(321, Type::cast('integer', '321'));
    }

    public function test_int(): void
    {
        self::assertSame(456, Type::cast('int', '456'));
    }

    public function test_bool(): void
    {
        self::assertTrue(Type::cast('bool', '1'));
    }

    public function test_boolean(): void
    {
        self::assertTrue(Type::cast('boolean', '1'));
    }

    public function test_float(): void
    {
        self::assertSame(456.00, Type::cast('float', '456'));
    }

    public function test_double(): void
    {
        self::assertSame(456.00, Type::cast('double', '456'));
    }

    public function test_real(): void
    {
        self::assertSame(456.00, Type::cast('real', '456'));
    }

    public function test_string(): void
    {
        self::assertSame('456', Type::cast('string', 456));
    }

    public function test_array(): void
    {
        self::assertSame(['456'], Type::cast('array', '456'));
    }

    public function test_object(): void
    {
        self::assertSame('456', Type::cast('object', '456')->scalar);
    }

    public function test_invalid_type(): void
    {
        self::assertSame('456', Type::cast('foobar', '456'));
    }
}
