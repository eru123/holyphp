<?php

namespace app\config;

use eru123\config\BaseConfig;

class Email extends BaseConfig
{
    public static $config = [
        'driver' => 'smtp',
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'username' => 'username',
        'password' => 'password'
    ];
}
