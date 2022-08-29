<?php

declare(strict_types=1);

namespace JsonRpc;

use JsonException;
use JsonRpc\Error\ErrorException;
use JsonRpc\Error\InternalError;
use JsonRpc\Error\InvalidRequest;
use JsonRpc\Error\MethodNotFound;
use JsonRpc\Error\ParseError;
use JsonRpc\Request\Notification;
use JsonRpc\Request\Request;
use JsonRpc\Response\Error;
use JsonRpc\Response\Response;
use RuntimeException;
use Throwable;

final class Server
{
    public function __construct(
        private readonly HandlerResolver $handlerResolver,
    ) {
    }

    /**
     * @throws RuntimeException if failed to encode response to JSON string
     */
    public function respond(string $json): string
    {
        try {
            $requests = $this->parseJsonRequest($json);
        } catch (ParseError|InvalidRequest $e) {
            return $this->encodeJson(new Error(null, $e->getCode(), $e->getMessage(), $e->getData()));
        }

        $isBatchRequest = true;

        if (!\is_array($requests)) {
            $isBatchRequest = false;

            $requests = [$requests];
        }

        $response = [];

        foreach ($requests as $request) {
            if ($request instanceof Notification) {
                $this->processNotification($request);

                continue;
            }

            try {
                $response[] = $this->processRequest($request);
            } catch (ErrorException $e) {
                $response[] = new Error($request->id, $e->getCode(), $e->getMessage(), $e->getData());
            }
        }

        if (\count($response) === 1 && !$isBatchRequest) {
            $response = current($response);
        }

        if ($response === []) {
            return '';
        }

        return $this->encodeJson($response);
    }

    /**
     * @throws MethodNotFound if request method handler can't be resolved
     * @throws ErrorException if request method handler failed to process request
     */
    private function processRequest(Request $request): Response
    {
        try {
            $handler = $this->handlerResolver->resolve($request->method);
        } catch (Throwable $e) {
            throw new MethodNotFound();
        }

        if (!$handler instanceof RequestHandler) {
            throw new MethodNotFound();
        }

        try {
            return new Response($request->id, $handler->handle($request));
        } catch (ErrorException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new InternalError();
        }
    }

    private function processNotification(Notification $notification): void
    {
        try {
            $handler = $this->handlerResolver->resolve($notification->method);
        } catch (Throwable $e) {
            return;
        }

        if (!$handler instanceof NotificationHandler) {
            return;
        }

        try {
            $handler->handle($notification);
        } catch (Throwable $e) {
            return;
        }
    }

    /**
     * @throws ParseError     if request can't be parsed due to invalid JSON string provided
     * @throws InvalidRequest if request validation failed
     *
     * @return array<Notification|Request>|Notification|Request
     */
    private function parseJsonRequest(string $json): Request|Notification|array
    {
        $data = $this->decodeJson($json);

        if (!\is_array($data)) {
            throw new InvalidRequest();
        }

        if (array_is_list($data)) {
            $response = [];

            foreach ($data as $part) {
                $response[] = $this->convertArrayToRequestObject($part);
            }

            return $response;
        }

        return $this->convertArrayToRequestObject($data);
    }

    /**
     * @param mixed[] $data
     *
     * @throws InvalidRequest if request validation failed
     */
    private function convertArrayToRequestObject(array $data): Request|Notification
    {
        if (!\array_key_exists('jsonrpc', $data) || $data['jsonrpc'] !== '2.0') {
            throw new InvalidRequest();
        }

        if (!\array_key_exists('method', $data) || !\is_string($data['method']) || $data['method'] === '') {
            throw new InvalidRequest();
        }

        if (\array_key_exists('params', $data) && !\is_array($data['params'])) {
            throw new InvalidRequest();
        }

        if (\array_key_exists('id', $data) && !(\is_int($data['id']) || \is_string($data['id']))) {
            throw new InvalidRequest();
        }

        if (($data['id'] ?? null) !== null) {
            return new Request($data['id'], $data['method'], $data['params'] ?? []);
        }

        return new Notification($data['method'], $data['params'] ?? []);
    }

    /**
     * @throws ParseError if request can't be parsed due to invalid JSON string provided
     */
    private function decodeJson(string $json): mixed
    {
        try {
            return json_decode(json: $json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ParseError();
        }
    }

    /**
     * @param array<Error|Response>|Error|Response $value
     *
     * @throws RuntimeException if failed to encode response to JSON string
     */
    private function encodeJson(array|Response|Error $value): string
    {
        try {
            return json_encode(value: $value, flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new RuntimeException(
                message: "Failed to encode response to JSON string: {$e->getMessage()}",
                previous: $e,
            );
        }
    }
}
