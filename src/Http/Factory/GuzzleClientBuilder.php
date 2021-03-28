<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http\Factory;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Http\OAuth2Provider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;

/**
 * @codeCoverageIgnore
 */
final class GuzzleClientBuilder
{
    private const OAUTH_2_ACCESS_TOKEN_URL = OAuth2Provider::ACCESS_TOKEN_URL;

    /**
     * @var Oauth1|OAuth2Middleware OAuth1 or Oauth2 Middleware
     */
    private $oAuthMiddleware;

    private function __construct($oAuthMiddleware)
    {
        $this->oAuthMiddleware = $oAuthMiddleware;
    }

    public static function withOAuth1(Configuration $config): self
    {
        return new self(
            new Oauth1(
                [
                    'consumer_key' => $config->getConsumerKey(),
                    'consumer_secret' => $config->getConsumerSecret(),
                    'token' => $config->getAccessToken(),
                    'token_secret' => $config->getAccessTokenSecret(),
                ]
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function withOAuth2(Configuration $config): self
    {
        $middleware = new OAuth2Middleware(
            new ClientCredentials(
                new Client(
                    [
                        'base_uri' => self::OAUTH_2_ACCESS_TOKEN_URL,
                    ]
                ),
                [
                    'client_id' => $config->getConsumerKey(),
                    'client_secret' => $config->getConsumerSecret(),
                ]
            )
        );

        return new self($middleware);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(): ClientInterface
    {
        $stack = HandlerStack::create();
        $stack->push($this->oAuthMiddleware);

        return new Client(
            [
                'handler' => $stack,
                'auth' => 'oauth',
            ]
        );
    }
}
