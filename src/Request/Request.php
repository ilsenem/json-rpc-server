<?php

declare(strict_types=1);

namespace JsonRpc\Request;

final class Request
{
    /**
     * @param array<string,mixed>|list<mixed> $params
     */
    public function __construct(
        public readonly int|string $id,
        public readonly string $method,
        public readonly array $params = [],
    ) {
    }
}
