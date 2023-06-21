<?php

namespace eru123\router;

class Helper
{
    /**
     * Check if Router Path match the URI
     * @param string $path The Router Path to match on the URI
     * @param string|null $uri The URI to match on the Router Path
     * @return bool
     */
    public static function match(string $path, ?string $uri = null): bool
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::path2rgx($path);
        return preg_match($rgx, $uri);
    }

    /**
     * Get request method
     * @return string
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Sanitize URI
     * @param string $uri The URI to sanitize
     * @return string
     */
    public static function sanitize(string $uri): string
    {
        $uri = preg_replace('/\?.*/', '', $uri);
        $uri = preg_replace('/\/+/', '/', $uri);
        $uri = preg_replace('/\/$/', '', $uri);
        return empty($uri) ? '/' : $uri;
    }

    /**
     * Get sanitized URI
     * @return string
     */
    public static function uri(): string
    {
        return static::sanitize($_SERVER['REQUEST_URI']);
    }

    /**
     * Convert Router Path to Regular Expression
     * @param string $path The Router Path to convert
     * @return string The Regular Expression
     */
    public static function path2rgx(string $path): string
    {
        $var = '/\$([a-zA-Z_]([a-zA-Z0-9_]+)?)/';
        $rgx = preg_replace('/\//', "\\\/", $path);
        $rgx = preg_replace($var, '(?P<$1>[^\/\?]+)', $rgx);
        return '/^' . $rgx . '$/';
    }

    /**
     * Convert Router Directory Path to Regular Expression
     * @param string $path The Router Directory Path to convert
     * @return string The Regular Expression
     */
    public static function file2rgx(string $path): string
    {
        $var = '/\$([a-zA-Z_]([a-zA-Z0-9_]+)?)/';
        $rgx = preg_replace('/\//', "\\\/", $path);
        $rgx = preg_replace('/\\\\\/$/', '', $rgx);
        $rgx = preg_replace($var, '(?P<$1>[^\/\?]+)', $rgx);
        return '/^' . $rgx . '(?P<file>\/?[^\?]+)?$/';
    }

    /**
     * Get Router Path Parameters
     * @param string $path The Router Path to get the parameters
     * @param string|null $uri The URI to get the parameters
     * @return array The parameters
     */
    public static function params(string $path, string $uri = null): array
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::path2rgx($path);
        preg_match($rgx, $uri, $matches);
        return array_filter($matches, function ($k) {
            return !is_numeric($k);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check if Router Directory Path match the URI
     * @param string $path The Router Directory Path to match on the URI
     * @param string|null $uri The URI to match on the Router Directory Path
     * @return bool
     */
    public static function matchdir(string $path, ?string $uri = null): bool
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::file2rgx($path);
        return preg_match($rgx, $uri);
    }

    /**
     * Get Router Directory Path Parameters
     * @param string $path The Router Directory Path to get the parameters
     * @param string|null $uri The URI to get the parameters
     * @return array The parameters
     */
    public static function file(string $path, ?string $uri = null): string|false
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::file2rgx($path);
        preg_match($rgx, $uri, $matches);
        return isset($matches['file']) ? $matches['file'] : false;
    }
}
