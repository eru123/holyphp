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

function post(string $key = null, $default = null)
{
    return ArrayUtil::get($_POST, $key, $default);
}

function post_set(string $key, $value)
{
    return ArrayUtil::set($_POST, $key, $value);
}

function get(string $key = null, $default = null)
{
    return ArrayUtil::get($_GET, $key, $default);
}

function get_set(string $key, $value)
{
    return ArrayUtil::set($_GET, $key, $value);
}

function request(string $key = null, $default = null)
{
    return ArrayUtil::get($_REQUEST, $key, $default);
}

function server(string $key = null, $default = null)
{
    return ArrayUtil::get($_SERVER, $key, $default);
}

function server_set(string $key, $value)
{
    return ArrayUtil::set($_SERVER, $key, $value);
}

function session(string $key = null, $default = null)
{
    return ArrayUtil::get($_SESSION, $key, $default);
}

function session_set(string $key, $value)
{
    return ArrayUtil::set($_SESSION, $key, $value);
}

function cookie(string $key = null, $default = null)
{
    return ArrayUtil::get($_COOKIE, $key, $default);
}

function cookie_set(string $key, $value)
{
    return ArrayUtil::set($_COOKIE, $key, $value);
}

function globals(string $key = null, $default = null)
{
    return ArrayUtil::get($GLOBALS, $key, $default);
}

function globals_set(string $key, $value)
{
    return ArrayUtil::set($GLOBALS, $key, $value);
}