<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait TweetLookup
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets-id
     */
    public function getTweet(string $tweetId, array $queryParameters)
    {
        return $this->getQuerier()
            ->get(sprintf('tweets/%s', $tweetId), $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets
     */
    public function getTweets(array $tweetIds, array $additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['ids' => $this->implodeParamValues($tweetIds)]);

        return $this->getQuerier()
            ->get('tweets', $this->withDefaultParams($queryParameters));
    }
}
