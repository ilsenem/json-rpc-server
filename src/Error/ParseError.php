<?php

declare(strict_types=1);

namespace JsonRpc\Error;

final class ParseError extends ErrorException
{
    public function __construct(mixed $data = null)
    {
        parent::__construct(-32700, 'Parse error', $data);
    }
}
