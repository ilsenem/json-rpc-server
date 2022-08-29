<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\Request\Request;
use JsonRpc\RequestHandler;
use RuntimeException;

final class ExceptionThrowingRequestHandler implements RequestHandler
{
    public function handle(Request $request): string
    {
        if ($request->params === []) {
            throw new RuntimeException('Something bad happen');
        }

        return '';
    }
}
