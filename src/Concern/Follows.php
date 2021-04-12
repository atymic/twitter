<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Contract\Twitter;
use Atymic\Twitter\Exception\ClientException;

trait Follows
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/get-users-id-following
     */
    public function getFollowing(string $userId, array $queryParameters)
    {
        return $this->getQuerier()
            ->withOAuth2Client()
            ->get(sprintf('users/%s/following', $userId), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/get-users-id-followers
     */
    public function getFollowers(string $userId, array $queryParameters)
    {
        return $this->getQuerier()
            ->withOAuth2Client()
            ->get(sprintf('users/%s/followers', $userId), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/post-users-source_user_id-following
     */
    public function follow(string $sourceUserId, string $targetUserId)
    {
        $parameters = [
            'target_user_id' => $targetUserId,
            Twitter::KEY_REQUEST_FORMAT => Twitter::REQUEST_FORMAT_JSON,
        ];

        return $this->getQuerier()
            ->withOAuth1Client()
            ->post(sprintf('users/%s/following', $sourceUserId), $this->withDefaultParams($parameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/delete-users-source_id-following
     */
    public function unfollow(string $sourceUserId, string $targetUserId)
    {
        return $this->getQuerier()
            ->withOAuth1Client()
            ->delete(sprintf('users/%s/following/%s', $sourceUserId, $targetUserId), $this->withDefaultParams());
    }
}
