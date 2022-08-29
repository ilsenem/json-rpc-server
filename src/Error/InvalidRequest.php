<?php

declare(strict_types=1);

namespace JsonRpc\Error;

final class InvalidRequest extends ErrorException
{
    public function __construct(mixed $data = null)
    {
        parent::__construct(-32600, 'Invalid Request', $data);
    }
}
