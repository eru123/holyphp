<?php

namespace eru123\router;

use Exception;

class Context
{
    protected $__data__ = [];

    public function __construct(array $data = [])
    {
        $this->__data__ = $data;
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $name)) {
            throw new Exception("Cannot override method $name");
        }

        $this->__data__[$name] = $value;
    }

    public function __get($name)
    {
        return $this->__data__[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->__data__[$name]);
    }

    public function __unset($name)
    {
        unset($this->__data__[$name]);
    }

    public function __call($name, $args)
    {
        if (isset($this->__data__[$name]) && is_callable($this->__data__[$name])) {
            return call_user_func_array($this->__data__[$name], $args);
        }

        throw new Exception("Method $name not found");
    }

    public function __invoke($name, $args)
    {
        if (isset($this->__data__[$name]) && is_callable($this->__data__[$name])) {
            return call_user_func_array($this->__data__[$name], $args);
        }

        throw new Exception("Method $name not found");
    }

    public function __toString()
    {
        return json_encode($this->__data__);
    }

    public function __debugInfo()
    {
        return $this->__data__;
    }

    public function __toArray()
    {
        return $this->__data__;
    }

    public function __toObject()
    {
        return (object) $this->__data__;
    }

    public function __toStdClass()
    {
        return (object) $this->__data__;
    }
}
