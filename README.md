# JSON-RPC Server

Library provides JSON-RPC server following
[2.0](https://www.jsonrpc.org/specification) specs.

## Not stable

Version `0.x` is unstable and API is subject to change.

## Requirements

* PHP >=8.1

## Installation

Use [composer](https://getcomposer.org) to install library:

```shell
composer require json-rpc/server
```

## Usage

Implement `HandlerResolver` interface to provide server with method
handlers. Create server and pass handler resolver. After that you
can call methods with `respond` server method. Both request and response
must be JSON strings. If server have no respond an empty string will be
returned.

```php
<?php

/**
 * Handler resolver
 */
final class CustomHandlerResponse implements \JsonRpc\HandlerResolver
{
    /**
    * @param array<string,\JsonRpc\RequestHandler|\JsonRpc\NotificationHandler> $handlers
     */
    public function __construct(private array $handlers = [])
    {}

    public function resolve(string $method) : \JsonRpc\RequestHandler|\JsonRpc\NotificationHandler{
        if (!array_key_exists($method, $this->handlers)) {
            throw new \JsonRpc\MethodHandlerNotFound($method);
        }

        return $this->handlers[$method];
    }
}

/**
* "sum" method handler
 */
final class SumMethodHandler implements \JsonRpc\RequestHandler {
    public function handle(\JsonRpc\Request\Request $request): int
    {
        return $request->params[0] + $request->params[1];
    }
}

$request = <<<JSON
    {
        "jsonrpc": "2.0",
        "method": "sum",
        "params": [1,2],
        "id": 1
    }
JSON;

$resolver = CustomHandlerResolver([
    'sum' => new SumMethodHandler(),
]);
$server = new Server($resolver);
$response = $server->respond($request);

/**
 * {
 *  "jsonrpc": "2.0",
 *  "result": 3,
 *  "id": 1
 * }
 */
```

## Development

1. Build development container:

    ```shell
   make buid
   ```
2. Make changes to `.env` file if needed.
3. `sh` inside container:

    ```shell
    make sh
    ```

From inside the container you can use various `make` recipes:

* `make test` — run PHPUnit tests.
* `make test-coverage` — generate Code Coverage report to `./coverage`
  directory.
* `make analyse` — run PHPStan static code analysis.
* `make format` — run PHP-CS-Fixer code formatter.
* `make format-preview` — preview PHP-CS-Fixer output before format.

## License

[MIT](./LICENSE)
