<?php

namespace Atymic\Twitter\Facade;

use Atymic\Twitter\Twitter as TwitterContract;
use Illuminate\Support\Facades\Facade;

/**
 * @codeCoverageIgnore
 */
class Twitter extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return TwitterContract::class;
    }
}
