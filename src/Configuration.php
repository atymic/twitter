<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Atymic\Twitter\Exception\InvalidConfigException;

class Configuration
{
    private const PACKAGE_NAME = 'atymic/twitter';

    /** @var string */
    protected $apiUrl;
    /** @var string */
    protected $uploadUrl;
    /** @var string */
    protected $apiVersion;

    /** @var null|string */
    protected $consumerKey;
    /** @var null|string */
    protected $consumerSecret;
    /** @var null|string */
    protected $accessToken;
    /** @var null|string */
    protected $accessTokenSecret;

    /** @var bool */
    protected $debugMode = false;
    /** @var null|string */
    protected $userAgent;

    /**
     * @param string      $apiUrl
     * @param string      $uploadUrl
     * @param string      $apiVersion
     * @param null|string $consumerKey
     * @param null|string $consumerSecret
     * @param null|string $accessToken
     * @param null|string $accessTokenSecret
     * @param bool        $debugMode
     * @param null|string $userAgent
     */
    public function __construct(
        string $apiUrl,
        string $uploadUrl,
        string $apiVersion,
        ?string $consumerKey,
        ?string $consumerSecret,
        ?string $accessToken = null,
        ?string $accessTokenSecret = null,
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

        $this->debugMode = $debugMode;
        $this->userAgent = $userAgent === null
            ? sprintf('%s v%s php v%s', self::PACKAGE_NAME, Twitter::VERSION, PHP_VERSION)
            : $userAgent;
    }

    /**
     * @param array $config
     *
     * @throws InvalidConfigException
     *
     * @return self
     */
    public static function fromLaravelConfiguration(array $config): self
    {
        if (!isset($config['api_url'], $config['upload_url'], $config['api_version'])) {
            throw new InvalidConfigException('Required configuration options missing');
        }

        return new self(
            $config['api_url'],
            $config['upload_url'],
            $config['api_version'],
            $config['consumer_key'],
            $config['consumer_secret'],
            $config['access_token'],
            $config['access_token_secret'],
            $config['debug']
        );
    }

    /**
     * @param string $accessToken
     * @param string $accessTokenSecret
     *
     * @return self
     */
    public function withOauthCredentials(string $accessToken, string $accessTokenSecret): self
    {
        $config = clone $this;
        $config->accessToken = $accessToken;
        $config->accessTokenSecret = $accessTokenSecret;

        return $config;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getUploadUrl(): string
    {
        return $this->uploadUrl;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return null|string
     */
    public function getConsumerKey(): ?string
    {
        return $this->consumerKey;
    }

    /**
     * @return null|string
     */
    public function getConsumerSecret(): ?string
    {
        return $this->consumerSecret;
    }

    /**
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return null|string
     */
    public function getAccessTokenSecret(): ?string
    {
        return $this->accessTokenSecret;
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * @return null|string
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
