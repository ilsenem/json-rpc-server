<?php

declare(strict_types=1);

namespace JsonRpc;

interface HandlerResolver
{
    public function resolve(string $method): RequestHandler|NotificationHandler;
}
