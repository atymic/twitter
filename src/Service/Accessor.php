<?php

declare(strict_types=1);

namespace Atymic\Twitter\Service;

use Atymic\Twitter\Concern\FilteredStream;
use Atymic\Twitter\Concern\Follows;
use Atymic\Twitter\Concern\HideReplies;
use Atymic\Twitter\Concern\SampledStream;
use Atymic\Twitter\Concern\SearchTweets;
use Atymic\Twitter\Concern\Timelines;
use Atymic\Twitter\Concern\TweetLookup;
use Atymic\Twitter\Concern\UserLookup;
use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Contract\Twitter as TwitterContract;
use InvalidArgumentException;

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

    private QuerierContract $querier;

    public function __construct(QuerierContract $querier)
    {
        $this->querier = $querier;
    }

    /**
     * @throws InvalidArgumentException
     * @see QuerierContract::usingCredentials()
     */
    public function usingCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self {
        return new self(
            $this->getQuerier()
                ->usingCredentials($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret)
        );
    }

    /**
     * @throws InvalidArgumentException
     * @see QuerierContract::usingConfiguration()
     */
    public function usingConfiguration(Configuration $configuration): self
    {
        return new self(
            $this->getQuerier()
                ->usingConfiguration($configuration)
        );
    }

    protected function getQuerier(): QuerierContract
    {
        return $this->querier;
    }
}
