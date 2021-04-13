<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\ClientException;

trait SampledStream
{
    use ApiV2Behavior;

    /**
     * @throws ClientException
     * @see Querier::getStream()
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/sampled-stream/api-reference/get-tweets-sample-stream
     */
    public function getSampledStream(callable $onTweet, array $parameters = []): void
    {
        $this->getQuerier()
            ->getStream('tweets/sample/stream', $onTweet, $parameters);
    }
}
