<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http;

use Atymic\Twitter\Contract\Configuration;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * @codeCoverageIgnore
 */
class OAuth2Provider extends GenericProvider
{
    public const ACCESS_TOKEN_URL = 'https://api.twitter.com/oauth2/token';

    public function __construct(Configuration $configuration)
    {
        parent::__construct(
            [
                'clientId' => $configuration->getConsumerKey(),
                'clientSecret' => $configuration->getConsumerSecret(),
                'urlAccessToken' => self::ACCESS_TOKEN_URL,
                'redirectUri' => 'http://my.example.com/your-redirect-url/',
                'urlAuthorize' => 'http://service.example.com/authorize',
                'urlResourceOwnerDetails' => 'http://service.example.com/resource',
            ]
        );
    }
}
