<?php

declare(strict_types=1);

namespace JsonRpc\Response;

use JsonSerializable;

final class Error implements JsonSerializable
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int $code,
        public readonly string $message,
        public readonly mixed $data = null,
    ) {
    }

    /**
     * @return array{
     *     jsonrpc: string,
     *     error: array{
     *         code: int,
     *         message: string,
     *         data?: mixed
     *     },
     *     id: int|string|null,
     * }
     */
    public function jsonSerialize(): array
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
            ],
            'id' => $this->id,
        ];

        if ($this->data !== null) {
            $response['error']['data'] = $this->data;
        }

        return $response;
    }
}
