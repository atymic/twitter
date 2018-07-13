<?php

namespace Thujohn\Twitter\Facades;

use Illuminate\Support\Facades\Facade;

class TwitterPremium extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    { 
        return 'Thujohn\Twitter\TwitterPremium';
    }

}