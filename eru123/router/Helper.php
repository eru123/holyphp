<?php

namespace eru123\router;

class Helper
{
    public static function match(string $path, ?string $uri = null): bool
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::path2rgx($path);
        return preg_match($rgx, $uri);
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function sanitize(string $uri): string
    {
        $uri = preg_replace('/\?.*/', '', $uri);
        $uri = preg_replace('/\/+/', '/', $uri);
        $uri = preg_replace('/\/$/', '', $uri);
        return empty($uri) ? '/' : $uri;
    }

    public static function uri(): string
    {
        return static::sanitize($_SERVER['REQUEST_URI']);
    }

    public static function path2rgx(string $path): string
    {
        $var = '/\$([a-zA-Z_]([a-zA-Z0-9_]+)?)/';
        $rgx = preg_replace('/\//', "\\\/", $path);
        $rgx = preg_replace($var, '(?P<$1>[^\/\?]+)', $rgx);
        return '/^' . $rgx . '$/';
    }

    public static function file2rgx(string $path): string
    {
        $var = '/\$([a-zA-Z_]([a-zA-Z0-9_]+)?)/';
        $rgx = preg_replace('/\//', "\\\/", $path);
        $rgx = preg_replace('/\\\\\/$/', '', $rgx);
        $rgx = preg_replace($var, '(?P<$1>[^\/\?]+)', $rgx);
        return '/^' . $rgx . '(?P<file>\/?[^\?]+)?$/';
    }

    public static function params(string $path, string $uri = null): array
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::path2rgx($path);
        preg_match($rgx, $uri, $matches);
        return array_filter($matches, function ($k) {
            return !is_numeric($k);
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function matchdir(string $path, ?string $uri = null): bool
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::file2rgx($path);
        return preg_match($rgx, $uri);
    }

    public static function file(string $path, ?string $uri = null): string|false
    {
        $uri = !empty($uri) ? static::sanitize($uri) : static::uri();
        $rgx = static::file2rgx($path);
        preg_match($rgx, $uri, $matches);
        return isset($matches['file']) ? $matches['file'] : false;
    }
}
