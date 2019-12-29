<?php

declare(strict_types=1);

namespace Patoui\Router;

class Headers
{
    /**
     * Get headers from $_SERVER global.
     * @return array<array>
     */
    public static function getHeadersArrayFromGlobals(): array
    {
        $headers = array_filter($_SERVER, [__CLASS__, 'isServerKeyAHeader'], ARRAY_FILTER_USE_KEY);
        $headers = array_map([__CLASS__, 'wrapValuesInArray'], $headers);
        $headerKeys = array_map('strval', array_keys($headers));
        $headerKeys = array_map([__CLASS__, 'stripKeyOfLeadingHttpPrefix'], $headerKeys);

        /** @var array<array> $headers */
        $headers = array_values($headers);

        /** @var array<string> $headerKeys */
        $headerKeys = array_map(static function ($headerKey) {
            return (string) $headerKey;
        }, array_values($headerKeys));

        $formattedHeaders = array_combine($headerKeys, $headers);

        if ($formattedHeaders === false) {
            return [];
        }

        return $formattedHeaders;
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
     * @return array<mixed>
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
