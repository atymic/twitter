<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait TweetCounts
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     *
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/counts/api-reference/get-tweets-counts-recent
     */
    public function countRecent(string $query, array $additionalParameters = [])
    {
        $queryParameters = array_merge($additionalParameters, ['query' => $query]);

        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/counts/recent', $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     *
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/counts/api-reference/get-tweets-counts-all
     */
    public function countAll(string $query, array $additionalParameters = [])
    {
        $queryParameters = array_merge($additionalParameters, ['query' => $query]);

        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/counts/all', $this->withDefaultParams($queryParameters));
    }
}
