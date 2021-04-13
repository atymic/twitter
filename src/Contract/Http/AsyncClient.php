<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract\Http;

use Atymic\Twitter\Exception\ClientException;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface AsyncClient extends Client
{
    public const EVENT_DATA = 'data';
    public const EVENT_ERROR = 'error';
    public const EVENT_CLOSE = 'close';

    public const KEY_REQUEST_HEADERS = 'headers';
    public const KEY_STREAM_CONTENTS = 'contents';
    public const KEY_STREAM_STOP_AFTER_SECONDS = 'stop_after_seconds';

    /**
     * @param array $parameters Array of parameters which may contain [headers](array), [stop_after_seconds](float)
     *
     * @throws ClientException
     */
    public function request(string $method, string $url, string $body = '', array $parameters = []): PromiseInterface;

    /**
     * @param array $parameters Array of parameters which may contain [contents](ReadableStreamInterface|string),
     *                          [stop_after_seconds](float), and [stop_after_count](int)
     *
     * @throws ClientException
     * @see Browser::requestStreaming()
     */
    public function stream(string $method, string $url, array $parameters = []): PromiseInterface;

    /**
     * Retrieve the underlying event loop.
     *
     * @return LoopInterface
     */
    public function loop(): LoopInterface;
}
