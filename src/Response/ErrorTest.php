<?php

declare(strict_types=1);

namespace JsonRpc\Response;

use JsonRpc\Test\JsonParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JsonRpc\Response\Error
 *
 * @internal
 */
final class ErrorTest extends TestCase
{
    use JsonParser;

    /**
     * @test
     */
    public function it_can_be_created(): void
    {
        $error = new Error(1, 1, 'Error message', ['context' => 'data']);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertSame(1, $error->id);
        $this->assertSame(1, $error->code);
        $this->assertSame('Error message', $error->message);
        $this->assertSame(['context' => 'data'], $error->data);
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_JSON_without_data(): void
    {
        $error = new Error(null, 1001, 'Error message');
        $json = $this->jsonEncode($error);

        $this->assertSame(
            $this->jsonEncode([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => 1001,
                    'message' => 'Error message',
                ],
                'id' => null,
            ]),
            $json,
        );
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_JSON_with_data(): void
    {
        $error = new Error(null, 1001, 'Error message', ['context' => 'data']);
        $json = $this->jsonEncode($error);

        $this->assertSame(
            $this->jsonEncode([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => 1001,
                    'message' => 'Error message',
                    'data' => [
                        'context' => 'data',
                    ],
                ],
                'id' => null,
            ]),
            $json,
        );
    }
}
