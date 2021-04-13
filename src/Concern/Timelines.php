<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait Timelines
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-tweets
     */
    public function userTweets(string $userId, array $queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%s/tweets', $userId), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-mentions
     */
    public function userMentions(string $userId, array $queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%s/mentions', $userId), $this->withDefaultParams($queryParameters));
    }
}
