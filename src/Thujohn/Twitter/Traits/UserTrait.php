<?php namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

Trait UserTrait {

	/**
	 * Returns fully-hydrated user objects for up to 100 users per request, as specified by comma-separated values passed to the user_id and/or screen_name parameters.
	 *
	 *  Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsersLookup($parameters = [])
	{
		if (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		{
			throw new BadMethodCallException("Parameter required missing : user_id or screen_name");
		}

		return $this->get('users/lookup', $parameters);
	}

	/**
	 * Returns a variety of information about the user specified by the required user_id or screen_name parameter. The author’s most recent Tweet will be returned inline when possible.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsers($parameters = [])
	{
		if (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : user_id or screen_name');
		}

		return $this->get('users/show', $parameters);
	}

	/**
	 * Provides a simple, relevance-based search interface to public user accounts on Twitter. Try querying by topical interest, full name, company name, location, or other criteria. Exact match searches are not supported.
	 *
	 * Parameters :
	 * - q
	 * - page
	 * - count
	 * - include_entities (0|1)
	 */
	public function getUsersSearch($parameters = [])
	{
		if (!array_key_exists('q', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : q');
		}

		return $this->get('users/search', $parameters);
	}

	/**
	 * Returns a map of the available size variations of the specified user’s profile banner. If the user has not uploaded a profile banner, a HTTP 404 will be served instead. This method can be used instead of string manipulation on the profile_banner_url returned in user objects as described in Profile Images and Banners.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 */
	public function getUserBanner($parameters = [])
	{
		return $this->get('users/profile_banner', $parameters);
	}

	/**
	 * Mutes the user specified in the ID parameter for the authenticating user.
	 *
	 *  Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function muteUser($parameters = [])
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new BadMethodCallException("Parameter required missing : screen_name or user_id");
		}

		return $this->post('mutes/users/create', $parameters);
	}

	/**
	 * Un-mutes the user specified in the ID parameter for the authenticating user.
	 *
	 *  Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function unmuteUser($parameters = [])
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new BadMethodCallException("Parameter required missing : screen_name or user_id");
		}

		return $this->post('mutes/users/destroy', $parameters);
	}

	/**
	 * Returns an array of numeric user ids the authenticating user has muted.
	 *
	 *  Parameters :
	 * - cursor
	 */
	public function mutedUserIds($parameters = [])
	{
		return $this->get('mutes/users/ids', $parameters);
	}

	/**
	 * Returns an array of user objects the authenticating user has muted.
	 *
	 *  Parameters :
	 * - cursor
	 * - include_entities
	 * - skip_status
	 */
	public function mutedUsers($parameters = [])
	{
		return $this->get('mutes/users/list', $parameters);
	}

	/**
	 * Access the users in a given category of the Twitter suggested user list.
	 *
	 * Parameters :
	 * - lang
	 */
	public function getSuggesteds($slug, $parameters = [])
	{
		return $this->get('users/suggestions/'.$slug, $parameters);
	}

	/**
	 * Access to Twitter’s suggested user list. This returns the list of suggested user categories. The category can be used in GET users / suggestions / :slug to get the users in that category.
	 *
	 * Parameters :
	 * - lang
	 */
	public function getSuggestions($parameters = [])
	{
		return $this->get('users/suggestions', $parameters);
	}

	/**
	 * Access the users in a given category of the Twitter suggested user list and return their most recent status if they are not a protected user.
	 */
	public function getSuggestedsMembers($slug, $parameters = [])
	{
		return $this->get('users/suggestions/'.$slug.'/members', $parameters);
	}

}
