<?php

use eru123\config\BaseConfig;
use eru123\helper\ArrayUtil;

function config(string $key, $default = null)
{
    return BaseConfig::get($key, $default);
}

function config_set(string $key, $value)
{
    return BaseConfig::set($key, $value);
}

function env(string $key = null, $default = null)
{
    return ArrayUtil::get($_ENV, $key, $default);
}

function env_set(string $key, $value)
{
    return ArrayUtil::set($_ENV, $key, $value);
}