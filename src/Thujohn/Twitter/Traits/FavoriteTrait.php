<?php namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

Trait FavoriteTrait {

	/**
	 * Returns the 20 most recent Tweets favorited by the authenticating or specified user.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - include_entities (0|1)
	 */
	public function getFavorites($parameters = [])
	{
		return $this->get('favorites/list', $parameters);
	}

	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user. Returns the un-favorited status in the requested format when successful.
	 *
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function destroyFavorite($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : id');
		}

		return $this->post('favorites/destroy', $parameters);
	}

	/**
	 * Favorites the status specified in the ID parameter as the authenticating user. Returns the favorite status when successful.
	 *
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function postFavorite($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : id');
		}

		return $this->post('favorites/create', $parameters);
	}

}
