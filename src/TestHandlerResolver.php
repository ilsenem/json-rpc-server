<?php

declare(strict_types=1);

namespace JsonRpc;

final class TestHandlerResolver implements HandlerResolver
{
    /**
     * @var array<string,NotificationHandler|RequestHandler>
     */
    private array $handlers = [];

    public function on(string $method, RequestHandler|NotificationHandler $handler): self
    {
        $this->handlers[$method] = $handler;

        return $this;
    }

    public function resolve(string $method): RequestHandler|NotificationHandler
    {
        if (!\array_key_exists($method, $this->handlers)) {
            throw new MethodHandlerNotFound($method);
        }

        return $this->handlers[$method];
    }
}
