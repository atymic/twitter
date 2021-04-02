<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http\Factory;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Http\AsyncClient as AsyncClientContract;
use Atymic\Twitter\Contract\Http\ClientFactory;
use Atymic\Twitter\Contract\Http\SyncClient as SyncClientContract;
use Atymic\Twitter\Http\Client\AsyncClient;
use Atymic\Twitter\Http\Client\SyncClient;
use Atymic\Twitter\Http\OAuth2Provider;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
final class ClientCreator implements ClientFactory
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createSyncClient(Configuration $config, bool $useOAuth2 = false): SyncClientContract
    {
        $builder = $useOAuth2 ? GuzzleClientBuilder::withOAuth2($config) : GuzzleClientBuilder::withOAuth1($config);

        return new SyncClient($builder->build(), $config->isDebugMode(), $this->logger);
    }

    public function createAsyncClient(Configuration $config): AsyncClientContract
    {
        return new AsyncClient(
            new BrowserCreator(),
            new OAuth2Provider($config),
            $config->isDebugMode(),
            null,
            $this->logger
        );
    }
}
