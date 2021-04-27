<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier;
use Atymic\Twitter\Twitter;
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
    ): Twitter {
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
    public function usingConfiguration(Configuration $configuration): Twitter
    {
        return $this->setQuerier(
            $this->getQuerier()
                ->usingConfiguration($configuration)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forApiV1(): Twitter
    {
        $config = $this->getQuerier()
            ->getConfiguration();
        $instance = clone $this;

        return $instance->usingConfiguration($config->forApiV1());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forApiV2(): Twitter
    {
        $config = $this->getQuerier()
            ->getConfiguration();
        $instance = clone $this;

        return $instance->usingConfiguration($config->forApiV2());
    }
}
