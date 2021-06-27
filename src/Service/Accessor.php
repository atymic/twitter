<?php

declare(strict_types=1);

namespace Atymic\Twitter\Service;

use Atymic\Twitter\Concern\FilteredStream;
use Atymic\Twitter\Concern\Follows;
use Atymic\Twitter\Concern\HideReplies;
use Atymic\Twitter\Concern\HotSwapper;
use Atymic\Twitter\Concern\SampledStream;
use Atymic\Twitter\Concern\SearchTweets;
use Atymic\Twitter\Concern\Timelines;
use Atymic\Twitter\Concern\TweetCounts;
use Atymic\Twitter\Concern\TweetLookup;
use Atymic\Twitter\Concern\UserLookup;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Contract\Twitter as TwitterContract;

final class Accessor implements TwitterContract
{
    use TweetLookup;
    use SearchTweets;
    use Timelines;
    use FilteredStream;
    use SampledStream;
    use UserLookup;
    use Follows;
    use HideReplies;
    use HotSwapper;
    use TweetCounts;

    private QuerierContract $querier;

    public function __construct(QuerierContract $querier)
    {
        $this->querier = $querier;
    }

    public function getQuerier(): QuerierContract
    {
        return $this->querier;
    }

    private function setQuerier(QuerierContract $querier): self
    {
        $this->querier = $querier;

        return $this;
    }
}
