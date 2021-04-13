<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Exception\InvalidConfigException;

interface Configuration
{
    public const KEY_API_URL = 'api_url';
    public const KEY_UPLOAD_URL = 'upload_url';
    public const KEY_API_VERSION = 'api_version';
    public const KEY_CONSUMER_KEY = 'consumer_key';
    public const KEY_CONSUMER_SECRET = 'consumer_secret';
    public const KEY_ACCESS_TOKEN = 'access_token';
    public const KEY_ACCESS_TOKEN_SECRET = 'access_token_secret';
    public const KEY_AUTHENTICATE_URL = 'authenticate_url';
    public const KEY_ACCESS_TOKEN_URL = 'access_token_url';
    public const KEY_REQUEST_TOKEN_URL = 'request_token_url';
    public const KEY_DEBUG = 'debug';

    /**
     * @throws InvalidConfigException
     */
    public static function createFromConfig(array $config): self;

    public function forApiV1(): self;

    public function forApiV2(): self;

    public function withOauthCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self;

    public function withoutOauthCredentials(bool $removeConsumerCredentials = false): self;

    public function getApiUrl(): string;

    public function getUploadUrl(): string;

    public function getApiVersion(): string;

    public function getConsumerKey(): ?string;

    public function getConsumerSecret(): ?string;

    public function getAccessToken(): ?string;

    public function getAccessTokenSecret(): ?string;

    public function getAuthenticateUrl(): ?string;

    public function getAccessTokenUrl(): ?string;

    public function getRequestTokenUrl(): ?string;

    public function isDebugMode(): bool;

    public function getUserAgent(): ?string;
}
