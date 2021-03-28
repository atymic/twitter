<?php

declare(strict_types=1);

namespace Atymic\Twitter\Factory;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\GuzzleClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;

final class GuzzleClientCreator implements GuzzleClientFactory
{
    private const OAUTH_2_ACCESS_TOKEN_URL = 'https://api.twitter.com/oauth2/token';

    private Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createClient(string $oAuthProtocol = self::AUTH_PROTOCOL_OAUTH_1): ClientInterface
    {
        $stack = HandlerStack::create();
        $oAuthMiddleware = $oAuthProtocol === self::AUTH_PROTOCOL_OAUTH_1
            ? $this->getOAuth1Middleware()
            : $this->getoAuth2Middleware();

        $stack->push($oAuthMiddleware);

        return new Client(
            [
                'stream' => true,
                'handler' => $stack,
                'auth' => 'oauth',
            ]
        );
    }

    private function getOAuth1Middleware(): Oauth1
    {
        return new Oauth1(
            [
                'consumer_key' => $this->config->getConsumerKey(),
                'consumer_secret' => $this->config->getConsumerSecret(),
                'token' => $this->config->getAccessToken(),
                'token_secret' => $this->config->getAccessTokenSecret(),
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getOAuth2Middleware(): OAuth2Middleware
    {
        $reAuthClient = new Client(
            [
                'base_uri' => self::OAUTH_2_ACCESS_TOKEN_URL,
            ]
        );
        $reAuthConfig = [
            'client_id' => $this->config->getConsumerKey(),
            'client_secret' => $this->config->getConsumerSecret(),
        ];

        return new OAuth2Middleware(new ClientCredentials($reAuthClient, $reAuthConfig));
    }
}
