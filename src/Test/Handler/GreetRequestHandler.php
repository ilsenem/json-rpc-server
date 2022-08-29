<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\Error\InvalidParams;
use JsonRpc\Request\Request;
use JsonRpc\RequestHandler;

final class GreetRequestHandler implements RequestHandler
{
    /**
     * @throws InvalidParams if 'name' param is missed or not a string
     */
    public function handle(Request $request): string
    {
        $this->validate($request->params);

        /** @var array{name: non-empty-string} $params */
        $params = $request->params;

        return "Hi, {$params['name']}!";
    }

    /**
     * @param mixed[] $params
     *
     * @throws InvalidParams if 'name' param is missing or not non-empty string
     */
    private function validate(array $params): void
    {
        if (!\array_key_exists('name', $params)) {
            throw new InvalidParams([
                'type' => 'required',
                'field' => 'params.name',
                'message' => '`params.name` is required',
            ]);
        }

        if (!\is_string($params['name'])) {
            throw new InvalidParams([
                'type' => 'string',
                'field' => 'params.name',
                'message' => '`params.name` must be a string',
            ]);
        }

        if ($params['name'] === '') {
            throw new InvalidParams([
                'type' => 'nonEmpty',
                'field' => 'params.name',
                'message' => '`params.name` must not be empty',
            ]);
        }
    }
}
