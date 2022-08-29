<?php

declare(strict_types=1);

namespace JsonRpc\Error;

final class InternalError extends ErrorException
{
    public function __construct(mixed $data = null)
    {
        parent::__construct(-32603, 'Internal error', $data);
    }
}
