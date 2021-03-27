<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;

trait Timelines
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-tweets
     */
    public function userTweets(int $userId, string ...$queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%d/tweets', $userId), $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-mentions
     */
    public function userMentions(int $userId, string ...$queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('users/%d/mentions', $userId), $queryParameters);
    }
}
