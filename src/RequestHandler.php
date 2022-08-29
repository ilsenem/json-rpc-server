<?php

declare(strict_types=1);

namespace JsonRpc;

use JsonRpc\Error\ErrorException;
use JsonRpc\Request\Request;

interface RequestHandler
{
    /**
     * @throws ErrorException if method handling failed with error
     */
    public function handle(Request $request): mixed;
}
