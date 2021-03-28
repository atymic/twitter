<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;

trait UserLookup
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-id
     */
    public function getUser(string $userId, string ...$queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%s', $userId), $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsers(array $userIds, string ...$additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['ids' => $this->implodeParamValues($userIds)]);

        return $this->getQuerier()
            ->get('users', $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-by-username-username
     */
    public function getUserByUsername(string $username, string ...$queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/by/username/%s', $username), $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsersByUsernames(array $usernames, string ...$additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['usernames' => $this->implodeParamValues($usernames)]);

        return $this->getQuerier()
            ->get('users/by', $queryParameters);
    }
}
