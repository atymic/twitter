<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract\Http;

use Atymic\Twitter\Contract\Configuration;
use InvalidArgumentException;

interface ClientFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function createSyncClient(Configuration $config, bool $useOAuth2 = false): SyncClient;

    public function createAsyncClient(Configuration $config): AsyncClient;
}
