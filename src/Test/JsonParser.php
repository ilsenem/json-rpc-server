<?php

declare(strict_types=1);

namespace JsonRpc\Test;

trait JsonParser
{
    private function jsonEncode(mixed $value): string
    {
        return json_encode(
            value: $value,
            flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }

    private function jsonDecode(string $json): mixed
    {
        return json_decode(
            json: $json,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
