<?php

declare(strict_types=1);

namespace Atymic\Twitter\Service;

use Atymic\Twitter\Concern\Follows;
use Atymic\Twitter\Concern\HideReplies;
use Atymic\Twitter\Concern\SearchTweets;
use Atymic\Twitter\Concern\Timelines;
use Atymic\Twitter\Concern\TweetLookup;
use Atymic\Twitter\Concern\UserLookup;
use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Contract\Twitter as TwitterContract;

final class Accessor implements TwitterContract
{
    use TweetLookup;
    use SearchTweets;
    use Timelines;
    use UserLookup;
    use Follows;
    use HideReplies;

    private QuerierContract $querier;

    public function __construct(QuerierContract $querier)
    {
        $this->querier = $querier;
    }

    public function usingCredentials(string $accessToken, string $accessTokenSecret): self
    {
        return new self(
            $this->getQuerier()
                ->usingCredentials($accessToken, $accessTokenSecret)
        );
    }

    protected function getQuerier(): QuerierContract
    {
        return $this->querier;
    }

    public function usingConfiguration(Configuration $configuration): self
    {
        return new self(
            $this->getQuerier()
                ->usingConfiguration($configuration)
        );
    }

    protected function implodeParamValues(array $paramValues): string
    {
        return implode(',', $paramValues);
    }
}
