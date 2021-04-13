<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Contract\Http\AsyncClient;
use Atymic\Twitter\Contract\Http\Client as HttpClient;
use Atymic\Twitter\Exception\ClientException as TwitterClientException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

interface Querier
{
    public const KEY_REQUEST_FORMAT = HttpClient::KEY_REQUEST_FORMAT;
    public const KEY_RESPONSE_FORMAT = HttpClient::KEY_RESPONSE_FORMAT;
    public const KEY_FORMAT = HttpClient::KEY_FORMAT;

    public const KEY_STREAM_CONTENTS = AsyncClient::KEY_STREAM_CONTENTS;
    public const KEY_STREAM_STOP_AFTER_SECONDS = AsyncClient::KEY_STREAM_STOP_AFTER_SECONDS;
    public const KEY_STREAM_STOP_AFTER_COUNT = 'stop_after_count';

    public const REQUEST_FORMAT_JSON = RequestOptions::JSON;
    public const REQUEST_FORMAT_MULTIPART = RequestOptions::MULTIPART;

    public const RESPONSE_FORMAT_ARRAY = HttpClient::RESPONSE_FORMAT_ARRAY;
    public const RESPONSE_FORMAT_OBJECT = HttpClient::RESPONSE_FORMAT_OBJECT;
    public const RESPONSE_FORMAT_JSON = HttpClient::RESPONSE_FORMAT_JSON;

    public const REQUEST_METHOD_GET = HttpClient::REQUEST_METHOD_GET;
    public const REQUEST_METHOD_POST = HttpClient::REQUEST_METHOD_POST;
    public const REQUEST_METHOD_PUT = HttpClient::REQUEST_METHOD_PUT;
    public const REQUEST_METHOD_DELETE = HttpClient::REQUEST_METHOD_DELETE;

    /**
     * Creates a new instance with given credentials.
     *
     * @throws InvalidArgumentException
     */
    public function usingCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self;

    /**
     * Creates a new instance with given configuration.
     *
     * @throws InvalidArgumentException
     */
    public function usingConfiguration(Configuration $configuration): self;

    public function withOAuth1Client(): self;

    public function withOAuth2Client(): self;

    /**
     * @throws TwitterClientException
     */
    public function query(
        string $endpoint,
        string $method = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        ?string $extension = null
    );

    /**
     * @throws TwitterClientException
     */
    public function directQuery(
        string $url,
        string $method = self::REQUEST_METHOD_GET,
        array $parameters = []
    );

    /**
     * @throws TwitterClientException
     */
    public function get(string $endpoint, array $parameters = [], ?string $extension = null);

    /**
     * @throws TwitterClientException
     */
    public function post(string $endpoint, array $parameters = [], bool $multipart = false);

    /**
     * @throws TwitterClientException
     */
    public function put(string $endpoint, array $parameters = []);

    /**
     * @throws TwitterClientException
     */
    public function delete(string $endpoint, array $parameters = []);

    /**
     * @param callable $onData Callable function which expects a chunk of data (string) as it's only param.
     *
     * @throws TwitterClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference API Reference: Filtered Stream
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/sampled-stream/introduction API Reference: Sampled Stream
     */
    public function getStream(string $endpoint, callable $onData, array $parameters = []): void;

    public function getConfiguration(): Configuration;
}
