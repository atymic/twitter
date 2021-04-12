<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait SearchTweets
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-recent
     */
    public function searchRecent(string $query, array $additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['query' => $query]);

        return $this->getQuerier()
            ->get('tweets/search/recent', $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-all
     */
    public function searchAll(string $query, array $additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['query' => $query]);

        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/search/all', $this->withDefaultParams($queryParameters));
    }
}
