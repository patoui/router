<?php

declare(strict_types=1);

namespace Patoui\Router;

class Type
{
    public const TYPE_INT     = 'int';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOL    = 'bool';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_FLOAT   = 'float';
    public const TYPE_DOUBLE  = 'double';
    public const TYPE_REAL    = 'real';
    public const TYPE_STRING  = 'string';
    public const TYPE_ARRAY   = 'array';
    public const TYPE_OBJECT  = 'object';

    public const INTEGER_TYPES = [
        self::TYPE_INT,
        self::TYPE_INTEGER,
    ];

    public const BOOLEAN_TYPES = [
        self::TYPE_BOOL,
        self::TYPE_BOOLEAN,
    ];

    public const FLOAT_TYPES = [
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
        self::TYPE_REAL,
    ];

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    public static function cast(string $type, $value)
    {
        if (in_array($type, self::INTEGER_TYPES, true)) {
            return (int) $value;
        }

        if (in_array($type, self::BOOLEAN_TYPES, true)) {
            return (bool) $value;
        }

        if (in_array($type, self::FLOAT_TYPES, true)) {
            return (float) $value;
        }

        if ($type === self::TYPE_STRING) {
            return (string) $value;
        }

        if ($type === self::TYPE_ARRAY) {
            return (array) $value;
        }

        if ($type === self::TYPE_OBJECT) {
            return (object) $value;
        }

        return $value;
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_INT,
            self::TYPE_INTEGER,
            self::TYPE_BOOL,
            self::TYPE_BOOLEAN,
            self::TYPE_FLOAT,
            self::TYPE_DOUBLE,
            self::TYPE_REAL,
            self::TYPE_STRING,
            self::TYPE_ARRAY,
            self::TYPE_OBJECT,
        ], true);
    }
}
