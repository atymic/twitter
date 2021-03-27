<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Exception\InvalidConfigException;

interface Configuration
{
    public static function createWithDefaults(): self;

    /**
     * @throws InvalidConfigException
     */
    public static function fromLaravelConfiguration(array $config): self;

    public function withOauthCredentials(string $accessToken, string $accessTokenSecret): self;

    public function withoutOauthCredentials(): self;

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
