<?php

namespace Atymic\Twitter\Facades;

use Atymic\Twitter\Twitter as TwitterClient;
use Illuminate\Support\Facades\Facade;

class Twitter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TwitterClient::class;
    }
}
