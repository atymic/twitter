<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract\Http;

use GuzzleHttp\RequestOptions;

interface Client
{
    public const KEY_HEADER_AUTH = 'Authorization';
    public const KEY_HEADER_CONTENT_TYPE = 'Content-Type';

    public const KEY_REQUEST_FORMAT = 'request_format';
    public const KEY_RESPONSE_FORMAT = 'response_format';
    public const KEY_FORMAT = 'format';

    public const REQUEST_FORMAT_JSON = RequestOptions::JSON;
    public const REQUEST_FORMAT_MULTIPART = RequestOptions::MULTIPART;

    public const RESPONSE_FORMAT_ARRAY = 'array';
    public const RESPONSE_FORMAT_OBJECT = 'object';
    public const RESPONSE_FORMAT_JSON = 'json';

    public const REQUEST_METHOD_GET = 'GET';
    public const REQUEST_METHOD_POST = 'POST';
    public const REQUEST_METHOD_PUT = 'PUT';
    public const REQUEST_METHOD_DELETE = 'DELETE';

    public const STREAM_BYTES_PER_READ = 1024;
}
