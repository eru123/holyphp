<?php

namespace eru123\router\static;

use eru123\router\Router as DynamicRouter;

class Router
{
    protected static $router;

    final protected static function getRouter()
    {
        if (!isset(self::$router)) {
            self::$router = new DynamicRouter;
        }
        return self::$router;
    }

    final public static function __callStatic($name, $args)
    {
        return self::getRouter()->$name(...$args);
    }
}
