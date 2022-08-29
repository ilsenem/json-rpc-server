<?php

declare(strict_types=1);

namespace JsonRpc\Error;

final class InvalidParams extends ErrorException
{
    public function __construct(mixed $data = null)
    {
        parent::__construct(-32602, 'Invalid params', $data);
    }
}
