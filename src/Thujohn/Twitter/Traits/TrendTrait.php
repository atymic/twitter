<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait TrendTrait {

	/**
	 * Returns the top 10 trending topics for a specific WOEID, if trending information is available for it.
	 *
	 * Parameters :
	 * - id
	 * - exclude
	 */
	public function getTrendsPlace($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new Exception('Parameter required missing : id');
		}

		return $this->get('trends/place', $parameters);
	}

	/**
	 * Returns the locations that Twitter has trending topic information for.
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
	 */
	public function getTrendsClosest($parameters = [])
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters))
		{
			throw new Exception('Parameter required missing : lat or long');
		}

		return $this->get('trends/closest', $parameters);
	}

}