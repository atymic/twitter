<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait UserLookup
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-id
     */
    public function getUser(string $userId, array $queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%s', $userId), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsers(array $userIds, array $additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['ids' => $this->implodeParamValues($userIds)]);

        return $this->getQuerier()
            ->get('users', $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-by-username-username
     */
    public function getUserByUsername(string $username, array $queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/by/username/%s', $username), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsersByUsernames(array $usernames, array $additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['usernames' => $this->implodeParamValues($usernames)]);

        return $this->getQuerier()
            ->get('users/by', $this->withDefaultParams($queryParameters));
    }
}
