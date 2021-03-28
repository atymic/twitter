<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Exception\RequestException as TwitterRequestException;
use GuzzleHttp\RequestOptions;

interface Querier
{
    public const KEY_REQUEST_FORMAT = 'request_format';
    public const KEY_RESPONSE_FORMAT = 'response_format';
    public const KEY_FORMAT = 'format';
    public const KEY_STREAM = 'stream';

    public const REQUEST_FORMAT_JSON = RequestOptions::JSON;
    public const REQUEST_FORMAT_MULTIPART = RequestOptions::MULTIPART;

    public const RESPONSE_FORMAT_ARRAY = 'array';
    public const RESPONSE_FORMAT_OBJECT = 'object';
    public const RESPONSE_FORMAT_JSON = 'json';

    public const REQUEST_METHOD_GET = 'GET';
    public const REQUEST_METHOD_POST = 'POST';
    public const REQUEST_METHOD_PUT = 'PUT';
    public const REQUEST_METHOD_DELETE = 'DELETE';

    public function usingCredentials(string $accessToken, string $accessTokenSecret): self;

    public function usingConfiguration(Configuration $configuration): self;

    public function withOAuth1Client(): self;

    public function withOAuth2Client(): self;

    /**
     * @throws TwitterRequestException
     */
    public function query(
        string $endpoint,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        ?string $extension = null
    );

    /**
     * @throws TwitterRequestException
     */
    public function directQuery(
        string $url,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = []
    );

    /**
     * @throws TwitterRequestException
     */
    public function get(string $endpoint, array $parameters = [], bool $multipart = false, ?string $extension = null);

    /**
     * @throws TwitterRequestException
     */
    public function post(string $endpoint, array $parameters = [], bool $multipart = false);

    /**
     * @throws TwitterRequestException
     */
    public function put(string $endpoint, array $parameters = []);

    /**
     * @throws TwitterRequestException
     */
    public function delete(string $endpoint, array $parameters = []);

    public function getConfiguration(): Configuration;
}
