<?php

namespace eru123\email\provider;

class Gmail extends SMTP implements ProviderInterface
{
    public function __construct(array $config = [])
    {
        $cfg = array_merge([
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'secure' => 'ssl',
        ], $config);
        parent::__construct($cfg);
    }
}
