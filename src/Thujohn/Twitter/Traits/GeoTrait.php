<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait GeoTrait {

	/**
	 * Returns all the information about a known place.
	 */
	public function getGeo($id)
	{
		return $this->get('geo/id/'.$id);
	}

	/**
	 * Given a latitude and a longitude, searches for up to 20 places that can be used as a place_id when updating a status.
	 *
	 * Parameters :
	 * - lat
	 * - long
	 * - accuracy
	 * - granularity (poi|neighborhood|city|admin|country)
	 * - max_results
	 * - callback
	 */
	public function getGeoReverse($parameters = [])
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters))
		{
			throw new Exception('Parameter required missing : lat or long');
		}

		return $this->get('geo/reverse_geocode', $parameters);
	}

	/**
	 * Search for places that can be attached to a statuses/update. Given a latitude and a longitude pair, an IP address, or a name, this request will return a list of all the valid places that can be used as the place_id when updating a status.
	 *
	 * Parameters :
	 * - lat
	 * - long
	 * - query
	 * - ip
	 * - granularity (poi|neighborhood|city|admin|country)
	 * - accuracy
	 * - max_results
	 * - contained_within
	 * - attribute:street_address
	 * - callback
	 */
	public function getGeoSearch($parameters = [])
	{
		return $this->get('geo/search', $parameters);
	}

	/**
	 * Locates places near the given coordinates which are similar in name. Conceptually you would use this method to get a list of known places to choose from first. Then, if the desired place doesn't exist, make a request to POST geo/place to create a new one. The token contained in the response is the token needed to be able to create a new place.
	 *
	 * Parameters :
	 * - lat
	 * - long
	 * - name
	 * - contained_within
	 * - attribute:street_address
	 * - callback
	 */
	public function getGeoSimilar($parameters = [])
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters) || !array_key_exists('name', $parameters))
		{
			throw new Exception('Parameter required missing : lat, long or name');
		}

		return $this->get('geo/similar_places', $parameters);
	}

}