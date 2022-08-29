<?php

declare(strict_types=1);

namespace JsonRpc\Error;

final class MethodNotFound extends ErrorException
{
    public function __construct(mixed $data = null)
    {
        parent::__construct(-32601, 'Method not found', $data);
    }
}
