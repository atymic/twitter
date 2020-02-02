<?php

declare(strict_types=1);

namespace Atymic\Twitter\Traits;

use Atymic\Twitter\Exception\AuthException;
use Atymic\Twitter\Twitter;

trait AuthTrait
{
    /**
     * Get a sign-in request token from Twitter.
     *
     * @param string $callbackUrl
     *
     * @throws AuthException
     * @return array
     */
    public function getRequestToken(string $callbackUrl): array
    {
        $tokenEndpoint = $this->config->getRequestTokenUrl() ?? '';
        $responseBody = $this->get(
            $tokenEndpoint,
            [
                Twitter::KEY_OAUTH_CALLBACK => $callbackUrl,
                Twitter::KEY_FORMAT => self::RESPONSE_FORMAT_JSON,
                Twitter::KEY_PREBUILT_ENDPOINT => true,
            ]
        );

        parse_str($responseBody, $token);
        if (isset($token[Twitter::KEY_OAUTH_TOKEN], $token[Twitter::KEY_OAUTH_TOKEN_SECRET])) {
            return $token;
        }

        throw new AuthException(sprintf('Failed to fetch request token. Response content: %s', $responseBody));
    }

    /**
     * @param array $requestToken
     *
     * @return string
     */
    public function getAuthenticateUrl(array $requestToken): string
    {
        $oauthToken = $requestToken[Twitter::KEY_OAUTH_TOKEN];

        return sprintf('%s?%s=%s', $this->config->getAuthenticateUrl(), Twitter::KEY_OAUTH_TOKEN, $oauthToken);
    }

    /**
     * Get an access token for a logged in user.
     *
     * @param string $oauthVerifier
     *
     * @throws AuthException
     * @return array
     */
    public function getAccessToken(string $oauthVerifier): array
    {
        $accessTokenEndpoint = $this->config->getAccessTokenUrl();
        $responseBody = $this->get(
            $accessTokenEndpoint,
            [
                Twitter::KEY_OAUTH_VERIFIER => $oauthVerifier,
                Twitter::KEY_FORMAT => self::RESPONSE_FORMAT_JSON,
                Twitter::KEY_PREBUILT_ENDPOINT => true,
            ]
        );

        parse_str($responseBody, $token);
        if (isset($token[Twitter::KEY_OAUTH_TOKEN], $token[Twitter::KEY_OAUTH_TOKEN_SECRET])) {
            return $token;
        }

        throw new AuthException(sprintf('Failed to fetch access token. Response content: %s', $responseBody));
    }
}