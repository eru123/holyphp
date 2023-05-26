<?php

namespace eru123\helper;

class Composer
{
    protected static $autoload = null;
    protected static $composer_path = null;
    protected static $classmap = null;
    protected static $config_classes = null;

    public static function set_composer_path(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Composer path does not exist: $path");
        }

        static::$composer_path = realpath($path);
    }

    public static function get_composer_path()
    {
        return static::$composer_path;
    }

    public static function get_autoload()
    {
        if (static::$autoload === null && static::$composer_path === null) {
            throw new \Exception("Composer path is not set, please set a composer path or autoload first");
        }

        if (static::$autoload === null) {
            static::$autoload = require_once static::$composer_path;
        }

        return static::$autoload;
    }

    public static function set_autoload(object $autoload)
    {
        static::$autoload = $autoload;
    }

    public static function get_classmap()
    {
        if (static::$classmap === null) {
            static::$classmap = [];
            $classmaps = static::get_autoload()->getClassMap();
            foreach ($classmaps as $class => $file) {
                if (!file_exists($file)) {
                    continue;
                }

                static::$classmap[$class] = realpath($file);
            }
        }

        return static::$classmap;
    }
}