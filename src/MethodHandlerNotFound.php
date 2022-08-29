<?php

declare(strict_types=1);

namespace JsonRpc;

use RuntimeException;

final class MethodHandlerNotFound extends RuntimeException
{
    public function __construct(string $method)
    {
        parent::__construct(
            message: "Handler for method {$method} not found"
        );
    }
}
