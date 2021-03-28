<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Exception\RequestException;
use Atymic\Twitter\Twitter;

trait SampledStream
{
    use ApiV2Behavior;

    /**
     * @throws RequestException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/sampled-stream/api-reference/get-tweets-sample-stream
     */
    public function getSampledStream(string ...$queryParameters)
    {
        $parameters = $queryParameters;
        $parameters[Twitter::KEY_STREAM] = true;

        return $this->getQuerier()
            ->withOAuth2Client()
            ->get('tweets/sample/stream', $parameters);
    }
}
