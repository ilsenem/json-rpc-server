<?php

declare(strict_types=1);

namespace JsonRpc\Request;

final class Notification
{
    /**
     * @param array<string,mixed>|list<mixed> $params
     */
    public function __construct(
        public readonly string $method,
        public readonly array $params = [],
    ) {
    }
}
