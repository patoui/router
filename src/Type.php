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
     */
    public static function cast(string $type, $value): void
    {
        $type = strtolower($type);

        if (in_array($type, self::INTEGER_TYPES, true)) {
            settype($value, $type);
            return;
        }

        if (in_array($type, self::BOOLEAN_TYPES, true)) {
            settype($value, $type);
            return;
        }

        if (in_array($type, self::FLOAT_TYPES, true)) {
            settype($value, $type);
            return;
        }

        if ($type === self::TYPE_STRING) {
            settype($value, $type);
            return;
        }

        if ($type === self::TYPE_ARRAY) {
            settype($value, $type);
            return;
        }

        if ($type === self::TYPE_OBJECT) {
            settype($value, $type);
            return;
        }
    }
}
