<?php

declare(strict_types=1);

namespace Atymic\Twitter\Exception;

use JsonException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
final class AuthException extends RuntimeException implements TwitterException
{
    /**
     * @see https://github.com/spatie/twitter-labs/blob/master/src/Exceptions/OauthException.php Adapted from spatie/twitter-labs
     */
    public static function fromIdentityProviderException(
        IdentityProviderException $identityProviderException
    ): AuthException {
        $responseBody = $identityProviderException->getResponseBody();

        try {
            if (is_array($responseBody)) {
                $responseBody = json_encode($responseBody, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            }
        } catch (JsonException $exception) {
            return new self(
                sprintf('Authentication failed with message: %s', $identityProviderException->getMessage())
            );
        }

        return new self(sprintf('Twitter API returned the following response:\n\r%s', $responseBody));
    }
}
