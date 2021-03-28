<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use GuzzleHttp\ClientInterface;
use InvalidArgumentException;

interface GuzzleClientFactory
{
    public const AUTH_PROTOCOL_OAUTH_1 = 'oAuth1';
    public const AUTH_PROTOCOL_OAUTH_2 = 'oAuth2';

    /**
     * @throws InvalidArgumentException
     */
    public function createClient(string $oAuthProtocol = self::AUTH_PROTOCOL_OAUTH_1): ClientInterface;
}
