<?php

declare(strict_types=1);

namespace Patoui\Router;

class Headers
{
    /* @var array */
    private $headers;

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * Get headers from $_SERVER global.
     * @return array
     */
    public static function getHeadersArrayFromGlobals(): array
    {
        $headers = array_filter($_SERVER, ['self', 'isServerKeyAHeader'], ARRAY_FILTER_USE_KEY);
        $headers = array_map(['self', 'wrapValuesInArray'], $headers);
        $headerKeys = array_map('strval', array_keys($headers));
        $headerKeys = array_map(['self', 'stripKeyOfLeadingHttpPrefix'], $headerKeys);
        $headerKeys = array_map('strval', array_values($headerKeys));

        return array_combine($headerKeys, $headers);
    }

    /**
     * @param string $serverParameter
     * @return bool
     */
    private static function isServerKeyAHeader(string $serverParameter): bool
    {
        return stripos($serverParameter, 'HTTP_') === 0;
    }

    /**
     * @param mixed $header
     * @return array
     */
    private static function wrapValuesInArray($header): array
    {
        return is_array($header) ? $header : [$header];
    }

    /**
     * @param mixed $headerKey
     * @return string
     */
    private static function stripKeyOfLeadingHttpPrefix($headerKey): string
    {
        return is_string($headerKey) ? str_replace('HTTP_', '', $headerKey) : '';
    }
}
