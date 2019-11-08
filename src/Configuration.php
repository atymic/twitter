<?php
declare(strict_types=1);

namespace Atymic\Twitter;

class Configuration
{
    /** @var string */
    protected $apiUrl;
    /** @var string */
    protected $uploadUrl;
    /** @var string */
    protected $apiVersion;

    /** @var string|null */
    protected $consumerKey;
    /** @var string|null */
    protected $consumerSecret;
    /** @var string|null */
    protected $accessToken;
    /** @var string|null */
    protected $accessTokenSecret;

    /** @var bool */
    protected $debugMode = false;
    /** @var string|null */
    protected $userAgent;

    /**
     * @param string      $apiUrl
     * @param string      $uploadUrl
     * @param string      $apiVersion
     * @param string|null $consumerKey
     * @param string|null $consumerSecret
     * @param string|null $accessToken
     * @param string|null $accessTokenSecret
     * @param bool        $debugMode
     * @param string|null $userAgent
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
            ? sprintf('atymic/twitter v%s php v%s', Twitter::VERSION, phpversion())
            : $userAgent;
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
     * @return string|null
     */
    public function getConsumerKey(): ?string
    {
        return $this->consumerKey;
    }

    /**
     * @return string|null
     */
    public function getConsumerSecret(): ?string
    {
        return $this->consumerSecret;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return string|null
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
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

}
