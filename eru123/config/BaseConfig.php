<?php

namespace eru123\config;

class BaseConfig {
    public static function config_classes() {
        $classes = get_declared_classes();
        $config_classes = [];
        foreach ($classes as $class) {
            if (is_subclass_of($class, __CLASS__)) {
                $config_classes[] = $class;
            }
        }
        return $classes;
        return $config_classes;
    }
}