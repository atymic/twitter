<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;
use Atymic\Twitter\Twitter;

trait FilteredStream
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream
     */
    public function getStream(string ...$queryParameters)
    {
        $parameters = $queryParameters;
        $parameters[Twitter::KEY_STREAM] = true;

        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/search/stream', $parameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/get-tweets-search-stream-rules
     */
    public function getStreamRules(string ...$queryParameters)
    {
        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/search/stream/rules', $queryParameters);
    }

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference/post-tweets-search-stream-rules
     */
    public function postStreamRules(array $parameters)
    {
        $parameters[Twitter::KEY_REQUEST_FORMAT] = Twitter::REQUEST_FORMAT_JSON;

        return $this->getQuerier()
            ->withOAuth2Client()
            ->post('tweets/search/stream/rules', $parameters);
    }
}
