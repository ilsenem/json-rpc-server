<?php

declare(strict_types=1);

namespace JsonRpc\Response;

use JsonRpc\Test\JsonParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JsonRpc\Response\Response
 *
 * @internal
 */
final class ResponseTest extends TestCase
{
    use JsonParser;

    /**
     * @test
     */
    public function it_can_be_created(): void
    {
        $result = new Response(1, 'result');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('result', $result->result);
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_JSON(): void
    {
        $error = new Response(1, 'result');
        $json = $this->jsonEncode($error);

        $this->assertSame(
            $this->jsonEncode([
                'jsonrpc' => '2.0',
                'result' => 'result',
                'id' => 1,
            ]),
            $json,
        );
    }
}
