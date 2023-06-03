<?php

namespace eru123\email\provider;

interface ProviderInterface
{
    public function send(array $data): bool;
}
