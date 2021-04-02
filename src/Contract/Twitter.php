<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Atymic\Twitter\Exception\ClientException;
use Atymic\Twitter\Twitter as BaseTwitterContract;

interface Twitter extends BaseTwitterContract
{
    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets-id
     */
    public function getTweet(string $tweetId, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets
     */
    public function getTweets(array $tweetIds, array $additionalParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-recent
     */
    public function searchRecent(string $query, array $additionalParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/search/api-reference/get-tweets-search-all
     */
    public function searchAll(string $query, array $additionalParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-tweets
     */
    public function userTweets(string $userId, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/timelines/api-reference/get-users-id-mentions
     */
    public function userMentions(string $userId, array $queryParameters);

    /**
     * @param callable $onTweet Callable function which expects a tweet (JSON) as it's only param.
     *
     * @throws ClientException
     * @see Querier::getStream()
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream
     */
    public function getStream(callable $onTweet, array $parameters = []): void;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream-rules
     */
    public function getStreamRules(array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/post-tweets-search-stream-rules
     */
    public function postStreamRules(array $parameters);

    /**
     * @param callable $onTweet Callable function which expects a tweet (JSON) as it's only param.
     *
     * @throws ClientException
     * @see Querier::getStream()
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/sampled-stream/api-reference/get-tweets-sample-stream
     */
    public function getSampledStream(callable $onTweet, array $parameters = []): void;

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-id
     */
    public function getUser(string $userId, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsers(array $userIds, array $additionalParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-by-username-username
     */
    public function getUserByUsername(string $username, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users
     */
    public function getUsersByUsernames(array $usernames, array $additionalParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/get-users-id-following
     */
    public function getFollowing(string $userId, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/get-users-id-followers
     */
    public function getFollowers(string $userId, array $queryParameters);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/post-users-source_user_id-following
     */
    public function follow(string $sourceUserId, string $targetUserId);

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/users/follows/api-reference/delete-users-source_id-following
     */
    public function unfollow(string $sourceUserId, string $targetUserId);

    /**
     * Hide or un-hide a reply to a Tweet.
     *
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/hide-replies/api-reference/put-tweets-id-hidden
     */
    public function hideTweet(string $tweetId, bool $hidden = true);
}
