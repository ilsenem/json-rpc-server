<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\Error\InvalidParams;
use JsonRpc\Request\Request;
use JsonRpc\RequestHandler;

final class SumRequestHandler implements RequestHandler
{
    public function handle(Request $request): int
    {
        $this->validate($request->params);

        /** @var list<int> $params */
        $params = $request->params;

        return $request->params[0] + $request->params[1];
    }

    /**
     * @param mixed[] $params
     *
     * @throws InvalidParams if params are not two integers
     */
    public function validate(array $params): void
    {
        if (!array_is_list($params)) {
            throw new InvalidParams([
                'type' => 'list',
                'field' => 'params',
                'message' => '`params` field must be a list',
            ]);
        }

        if (\count($params) !== 2) {
            throw new InvalidParams([
                'type' => 'count',
                'field' => 'params',
                'message' => '`params` field must be the list with two elements',
            ]);
        }

        if (!\is_int($params[0]) || !\is_int($params[1])) {
            throw new InvalidParams([
                'type' => 'int',
                'field' => 'params',
                'message' => '`params` must be the list with two integers',
            ]);
        }
    }
}
