<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract\Http;

use Atymic\Twitter\Exception\ClientException;

interface SyncClient extends Client
{
    /**
     * @return mixed
     * @throws ClientException
     */
    public function request(string $method, string $url, array $data = []);
}
