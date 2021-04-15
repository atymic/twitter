<?php

namespace Atymic\Twitter\ApiV1\Traits;

use BadMethodCallException;

trait TrendTrait
{
    /**
     * Returns the top 10 trending topics for a specific WOEID, if trending information is available for it.
     *
     * Parameters :
     * - id
     * - exclude
     *
     * @param mixed $parameters
     */
    public function getTrendsPlace($parameters = [])
    {
        if (!array_key_exists('id', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : id');
        }

        return $this->get('trends/place', $parameters);
    }

    /**
     * Returns the locations that Twitter has trending topic information for.
     *
     * @param mixed $parameters
     */
    public function getTrendsAvailable($parameters = [])
    {
        return $this->get('trends/available', $parameters);
    }

    /**
     * Returns the locations that Twitter has trending topic information for, closest to a specified location.
     *
     * Parameters :
     * - lat
     * - long
     *
     * @param mixed $parameters
     */
    public function getTrendsClosest($parameters = [])
    {
        if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : lat or long');
        }

        return $this->get('trends/closest', $parameters);
    }
}
