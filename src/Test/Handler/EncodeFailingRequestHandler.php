<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\Request\Request;
use JsonRpc\RequestHandler;

final class EncodeFailingRequestHandler implements RequestHandler
{
    public function handle(Request $request): float
    {
        return NAN;
    }
}
