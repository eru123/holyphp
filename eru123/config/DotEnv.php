<?php

namespace eru123\config;

use Exception;

class DotEnv
{
    public static function load(string $path): void
    {
        $path = realpath($path);

        if (!$path) {
            throw new Exception('File not found');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);

            if (strpos($value, '#') !== false) {
                $value = substr($value, 0, strpos($value, '#'));
            }

            $name = trim($name);
            $value = trim($value);

            if (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            } else if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
                $value = str_replace('\n', "\n", $value);
                $value = str_replace('\r', "\r", $value);
                $value = str_replace('\t', "\t", $value);
                $value = str_replace('\v', "\v", $value);
                $value = str_replace('\e', "\e", $value);
                $value = str_replace('\f', "\f", $value);
                $value = str_replace('\$', "\$", $value);
                $value = str_replace('\0', "\0", $value);
            }

            if ($value === 'true') {
                $value = true;
            } else if ($value === 'false') {
                $value = false;
            } else if ($value === 'null') {
                $value = null;
            } else if (is_numeric($value)) {
                $value = $value + 0;
            } else if (empty($value)) {
                continue;
            }

            env_set($name, $value);
        }
    }
}
