<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\ApiV1\Contract\Twitter as TwitterV1Contract;
use Atymic\Twitter\ApiV1\Service\Twitter as TwitterV1;
use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier;
use Atymic\Twitter\Contract\Twitter as TwitterV2Contract;
use Atymic\Twitter\Service\Accessor as TwitterV2;
use Atymic\Twitter\Twitter as TwitterBaseContract;
use InvalidArgumentException;

trait HotSwapper
{
    abstract public function getQuerier(): Querier;

    /**
     * @throws InvalidArgumentException
     */
    public function usingCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): TwitterBaseContract {
        return $this->setQuerier(
            $this->getQuerier()
                ->usingCredentials(
                    $accessToken,
                    $accessTokenSecret,
                    $consumerKey,
                    $consumerSecret
                )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function usingConfiguration(Configuration $configuration): TwitterBaseContract
    {
        return $this->setQuerier(
            $this->getQuerier()
                ->usingConfiguration($configuration)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forApiV1(): TwitterV1Contract
    {
        $config = $this->getQuerier()
            ->getConfiguration()
            ->forApiV1();

        return new TwitterV1(
            $this->getQuerier()
                ->usingConfiguration($config)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forApiV2(): TwitterV2Contract
    {
        $config = $this->getQuerier()
            ->getConfiguration()
            ->forApiV2();

        return new TwitterV2(
            $this->getQuerier()
                ->usingConfiguration($config)
        );
    }
}
