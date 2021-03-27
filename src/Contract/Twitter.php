<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Exception\RequestException;
use Atymic\Twitter\Twitter as BaseTwitterContract;

interface Twitter extends BaseTwitterContract
{
    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets-id
     */
    public function getTweet(string $tweetId, string ...$queryParameters);

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets
     */
    public function getTweets(array $tweetIds, string ...$additionalParameters);

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-recent
     */
    public function searchRecent(string $query, string ...$additionalParameters);

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-tweets
     */
    public function userTweets(int $userId, string ...$queryParameters);

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-mentions
     */
    public function userMentions(int $userId, string ...$queryParameters);

    /**
     * Hide or un-hide a reply to a Tweet.
     *
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/hide-replies/api-reference/put-tweets-id-hidden
     */
    public function hideTweet(string $tweetId, bool $hidden = true);
}
