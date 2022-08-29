<?php

declare(strict_types=1);

namespace JsonRpc\Response;

use JsonSerializable;

final class Response implements JsonSerializable
{
    public function __construct(
        public readonly int|string $id,
        public readonly mixed $result,
    ) {
    }

    /**
     * @return array{
     *     jsonrpc: string,
     *     result: mixed,
     *     id: int|string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'jsonrpc' => '2.0',
            'result' => $this->result,
            'id' => $this->id,
        ];
    }
}
