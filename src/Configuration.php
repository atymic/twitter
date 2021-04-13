<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Atymic\Twitter\Contract\Configuration as ConfigurationContract;
use Atymic\Twitter\Exception\InvalidConfigException;

final class Configuration implements ConfigurationContract
{
    private const PACKAGE_NAME = 'atymic/twitter';

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

    /**
     * @throws InvalidConfigException
     */
    public static function createFromConfig(array $config): self
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

    public function forApiV1(): self
    {
        $instance = clone $this;
        $instance->apiVersion = Twitter::API_VERSION_1;

        return $instance;
    }

    public function forApiV2(): self
    {
        $instance = clone $this;
        $instance->apiVersion = Twitter::API_VERSION_2;

        return $instance;
    }

    public function withOauthCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self {
        $config = clone $this;
        $config->accessToken = $accessToken;
        $config->accessTokenSecret = $accessTokenSecret;
        $config->consumerKey = $consumerKey ?? $config->consumerKey;
        $config->consumerSecret = $consumerSecret ?? $config->consumerSecret;

        return $config;
    }

    public function withoutOauthCredentials(bool $removeConsumerCredentials = false): self
    {
        $config = clone $this;
        $config->accessToken = null;
        $config->accessTokenSecret = null;

        if ($removeConsumerCredentials) {
            $config->consumerKey = null;
            $config->consumerSecret = null;
        }

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
