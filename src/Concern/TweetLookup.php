<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;

trait TweetLookup
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets-id
     */
    public function getTweet(string $tweetId, string ...$queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('tweets/%s', $tweetId), $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets
     */
    public function getTweets(array $tweetIds, string ...$additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['ids' => $this->implodeParamValues($tweetIds)]);

        return $this->getQuerier()
            ->get('tweets', $queryParameters);
    }
}
