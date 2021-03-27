<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;

trait SearchTweets
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-recent
     */
    public function searchRecent(string $query, string ...$additionalParameters)
    {
        $queryParameters = array_merge($additionalParameters, ['query' => $query]);

        return $this->getQuerier()
            ->get('tweets/search/recent', $queryParameters);
    }
}
