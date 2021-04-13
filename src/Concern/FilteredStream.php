<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;
use Atymic\Twitter\Twitter;

trait FilteredStream
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see Querier::getStream()
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream
     */
    public function getStream(callable $onTweet, array $parameters = []): void
    {
        $this->getQuerier()
            ->getStream('tweets/search/stream', $onTweet, $parameters);
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream-rules
     */
    public function getStreamRules(array $queryParameters)
    {
        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/search/stream/rules', $this->withDefaultParams($queryParameters));
    }

    /**
     * @throws ClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/post-tweets-search-stream-rules
     */
    public function postStreamRules(array $parameters)
    {
        $parameters[Twitter::KEY_REQUEST_FORMAT] = Twitter::REQUEST_FORMAT_JSON;

        return $this->getQuerier()
            ->withOAuth2Client()
            ->post('tweets/search/stream/rules', $this->withDefaultParams($parameters));
    }
}
