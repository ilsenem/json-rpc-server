<?php

declare(strict_types=1);

namespace JsonRpc\Error;

use Exception;

abstract class ErrorException extends Exception
{
    public function __construct(int $code, string $message, private readonly mixed $data = null)
    {
        parent::__construct($message, $code);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
