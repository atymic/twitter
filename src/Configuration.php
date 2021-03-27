<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Atymic\Twitter\Contract\Configuration as ConfigurationContract;
use Atymic\Twitter\Exception\InvalidConfigException;

final class Configuration implements ConfigurationContract
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

    private const PACKAGE_NAME = 'atymic/twitter';
    private const DEFAULT_API_URL = Twitter::API_DOMAIN;
    private const DEFAULT_UPLOAD_URL = 'upload.twitter.com';
    private const DEFAULT_API_VERSION = Twitter::API_VERSION_1;

    protected string $apiUrl;
    protected string $uploadUrl;
    protected string $apiVersion;
    protected ?string $consumerKey;
    protected ?string $consumerSecret;
    protected ?string $accessToken;
    protected ?string $accessTokenSecret;
    protected bool $debugMode = false;
    protected ?string $userAgent;
    private ?string $authenticateUrl;
    private ?string $accessTokenUrl;
    private ?string $requestTokenUrl;

    public function __construct(
        string $apiUrl,
        string $uploadUrl,
        string $apiVersion,
        ?string $consumerKey,
        ?string $consumerSecret,
        ?string $accessToken = null,
        ?string $accessTokenSecret = null,
        ?string $authenticateUrl = null,
        ?string $accessTokenUrl = null,
        ?string $requestTokenUrl = null,
        bool $debugMode = false,
        ?string $userAgent = null
    ) {
        $this->apiUrl = $apiUrl;
        $this->uploadUrl = $uploadUrl;
        $this->apiVersion = $apiVersion;

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;

        $this->authenticateUrl = $authenticateUrl;
        $this->accessTokenUrl = $accessTokenUrl;
        $this->requestTokenUrl = $requestTokenUrl;

        $this->debugMode = $debugMode;
        $this->userAgent = $userAgent ?? sprintf('%s v%s php v%s', self::PACKAGE_NAME, Twitter::VERSION, PHP_VERSION);
    }

    public static function createWithDefaults(): self
    {
        return new self(self::DEFAULT_API_URL, self::DEFAULT_UPLOAD_URL, self::DEFAULT_API_VERSION, null, null);
    }

    /**
     * @throws InvalidConfigException
     */
    public static function fromLaravelConfiguration(array $config): self
    {
        if (!isset($config[self::KEY_API_URL], $config[self::KEY_UPLOAD_URL], $config[self::KEY_API_VERSION])) {
            throw new InvalidConfigException('Required configuration options missing!');
        }

        return new self(
            $config[self::KEY_API_URL],
            $config[self::KEY_UPLOAD_URL],
            $config[self::KEY_API_VERSION],
            $config[self::KEY_CONSUMER_KEY],
            $config[self::KEY_CONSUMER_SECRET],
            $config[self::KEY_ACCESS_TOKEN],
            $config[self::KEY_ACCESS_TOKEN_SECRET],
            $config[self::KEY_AUTHENTICATE_URL],
            $config[self::KEY_ACCESS_TOKEN_URL],
            $config[self::KEY_REQUEST_TOKEN_URL],
            $config[self::KEY_DEBUG]
        );
    }

    public function withOauthCredentials(string $accessToken, string $accessTokenSecret): self
    {
        $config = clone $this;
        $config->accessToken = $accessToken;
        $config->accessTokenSecret = $accessTokenSecret;

        return $config;
    }

    public function withoutOauthCredentials(): self
    {
        $config = clone $this;
        $config->accessToken = null;
        $config->accessTokenSecret = null;

        return $config;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getUploadUrl(): string
    {
        return $this->uploadUrl;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getConsumerKey(): ?string
    {
        return $this->consumerKey;
    }

    public function getConsumerSecret(): ?string
    {
        return $this->consumerSecret;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getAccessTokenSecret(): ?string
    {
        return $this->accessTokenSecret;
    }

    public function getAuthenticateUrl(): ?string
    {
        return $this->authenticateUrl;
    }

    public function getAccessTokenUrl(): ?string
    {
        return $this->accessTokenUrl;
    }

    public function getRequestTokenUrl(): ?string
    {
        return $this->requestTokenUrl;
    }

    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
