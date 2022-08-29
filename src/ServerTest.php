<?php

declare(strict_types=1);

namespace JsonRpc;

use JsonRpc\Test\Handler\EmailNotificationHandler;
use JsonRpc\Test\Handler\EncodeFailingRequestHandler;
use JsonRpc\Test\Handler\ExceptionThrowingNotificationHandler;
use JsonRpc\Test\Handler\ExceptionThrowingRequestHandler;
use JsonRpc\Test\Handler\GreetRequestHandler;
use JsonRpc\Test\Handler\SumRequestHandler;
use JsonRpc\Test\JsonParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \JsonRpc\Error\ErrorException
 * @covers \JsonRpc\Error\InternalError
 * @covers \JsonRpc\Error\InvalidParams
 * @covers \JsonRpc\Error\InvalidRequest
 * @covers \JsonRpc\Error\MethodNotFound
 * @covers \JsonRpc\Error\ParseError
 * @covers \JsonRpc\MethodHandlerNotFound
 * @covers \JsonRpc\Request\Notification
 * @covers \JsonRpc\Request\Request
 * @covers \JsonRpc\Response\Error
 * @covers \JsonRpc\Response\Response
 * @covers \JsonRpc\Server
 *
 * @internal
 */
final class ServerTest extends TestCase
{
    use JsonParser;

    /**
     * @test
     */
    public function it_responds_on_request_with_positional_params(): void
    {
        $server = $this->createServer(['sum' => new SumRequestHandler()]);
        $response = $this->callServerMethod($server, 'sum', [1, 2], 1);

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'result' => 3,
                'id' => 1,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_on_request_with_named_params(): void
    {
        $server = $this->createServer(['greet' => new GreetRequestHandler()]);
        $response = $this->callServerMethod($server, 'greet', ['name' => 'User'], 1);

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'result' => 'Hi, User!',
                'id' => 1,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_to_batch_request(): void
    {
        $server = $this->createServer([
            'sum' => new SumRequestHandler(),
            'greet' => new GreetRequestHandler(),
            'email' => new EmailNotificationHandler(),
        ]);
        $response = $server->respond($this->jsonEncode([
            [
                'jsonrpc' => '2.0',
                'method' => 'sum',
                'params' => [1, 2],
                'id' => 1,
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'greet',
                'params' => [
                    'name' => 'User',
                ],
                'id' => 2,
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'email',
            ],
        ]));

        $this->assertSame(
            [
                [
                    'jsonrpc' => '2.0',
                    'result' => 3,
                    'id' => 1,
                ],
                [
                    'jsonrpc' => '2.0',
                    'result' => 'Hi, User!',
                    'id' => 2,
                ],
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_no_error_on_notification_if_method_handler_not_found(): void
    {
        $server = $this->createServer([]);
        $response = $this->callServerMethod($server, 'unimplemented', [], null);

        $this->assertSame('', $response);
    }

    /**
     * @test
     */
    public function it_responds_with_no_error_on_notification_failed(): void
    {
        $server = $this->createServer(['failing' => new ExceptionThrowingNotificationHandler()]);
        $response = $this->callServerMethod($server, 'failing', [], null);

        $this->assertSame('', $response);
    }

    /**
     * @test
     */
    public function it_responds_with_error_on_request_if_method_handler_not_found(): void
    {
        $server = $this->createServer([]);
        $response = $this->callServerMethod($server, 'unimplemented', [], 1);

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32601,
                    'message' => 'Method not found',
                ],
                'id' => 1,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_error_on_invalid_request(): void
    {
        $server = $this->createServer([]);
        $noJsonRpcField = $server->respond($this->jsonEncode(['jsonrpc' => '1.0', 'method' => 'test']));
        $noMethodField = $server->respond($this->jsonEncode(['jsonrpc' => '2.0']));
        $invalidParamsField = $server->respond($this->jsonEncode([
            'jsonrpc' => '2.0',
            'method' => 'test',
            'params' => '',
        ]));
        $invalidIdField = $server->respond($this->jsonEncode([
            'jsonrpc' => '2.0',
            'method' => 'test',
            'id' => 1.7,
        ]));

        foreach ([$noJsonRpcField, $noMethodField, $invalidParamsField, $invalidIdField] as $response) {
            $this->assertSame(
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32600,
                        'message' => 'Invalid Request',
                    ],
                    'id' => null,
                ],
                $this->jsonDecode($response),
            );
        }
    }

    /**
     * @test
     */
    public function it_responds_with_error_on_invalid_params(): void
    {
        $server = $this->createServer(['greet' => new GreetRequestHandler()]);
        $response = $this->callServerMethod($server, 'greet', ['foo' => 'bar'], 1);

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid params',
                    'data' => [
                        'type' => 'required',
                        'field' => 'params.name',
                        'message' => '`params.name` is required',
                    ],
                ],
                'id' => 1,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_parse_error_of_invalid_JSON_request(): void
    {
        $server = $this->createServer([]);
        $response = $server->respond('{');

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32700,
                    'message' => 'Parse error',
                ],
                'id' => null,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_method_not_found_error_on_handler_type_mismatch(): void
    {
        $server = $this->createServer([
            'email' => new EmailNotificationHandler(),
            'greet' => new GreetRequestHandler(),
        ]);
        $response = $server->respond($this->jsonEncode([
            [
                'jsonrpc' => '2.0',
                'method' => 'email',
                'id' => 1,
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'greet',
            ],
        ]));

        $this->assertSame(
            [
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32601,
                        'message' => 'Method not found',
                    ],
                    'id' => 1,
                ],
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_internal_error_on_handler_failing(): void
    {
        $server = $this->createServer(['failing' => new ExceptionThrowingRequestHandler()]);
        $response = $this->callServerMethod($server, 'failing', [], 1);

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error',
                ],
                'id' => 1,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_responds_with_error_on_invalid_JSON_struct(): void
    {
        $server = $this->createServer([]);
        $response = $server->respond('""');

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid Request',
                ],
                'id' => null,
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @test
     */
    public function it_throws_runtime_exception(): void
    {
        $this->expectException(RuntimeException::class);

        $server = $this->createServer(['failing' => new EncodeFailingRequestHandler()]);

        $this->callServerMethod($server, 'failing', [], 1);
    }

    /**
     * @test
     */
    public function it_responds_correctly_on_batch_request_with_one_item(): void
    {
        $server = $this->createServer(['sum' => new SumRequestHandler()]);
        $response = $server->respond($this->jsonEncode([[
            'jsonrpc' => '2.0',
            'method' => 'sum',
            'params' => [1, 2],
            'id' => 1,
        ]]));

        $this->assertSame(
            [
                [
                    'jsonrpc' => '2.0',
                    'result' => 3,
                    'id' => 1,
                ],
            ],
            $this->jsonDecode($response),
        );
    }

    /**
     * @param array<string,NotificationHandler|RequestHandler> $methodToHandlerMap
     */
    private function createServer(array $methodToHandlerMap): Server
    {
        $resolver = new TestHandlerResolver();

        foreach ($methodToHandlerMap as $method => $handler) {
            $resolver->on($method, $handler);
        }

        return new Server($resolver);
    }

    /**
     * @param array<string,mixed>|list<mixed> $params
     */
    private function callServerMethod(Server $server, string $method, array $params, int|string|null $id): string
    {
        $request = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
        ];

        if ($id !== null) {
            $request['id'] = $id;
        }

        return $server->respond($this->jsonEncode($request));
    }
}
