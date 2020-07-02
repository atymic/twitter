<?php

namespace Atymic\Twitter\Facades;

use Atymic\Twitter\Twitter as TwitterClient;
use Illuminate\Support\Facades\Facade;

class Twitter extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return TwitterClient::class;
    }
}
