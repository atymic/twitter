<?php namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

Trait FriendshipTrait {

	/**
	 * Returns a collection of user_ids that the currently authenticated user does not want to receive retweets from.
	 *
	 * Parameters :
	 * - stringify_ids (0|1)
	 */
	public function getNoRters($parameters = [])
	{
		return $this->get('friendships/no_retweets/ids', $parameters);
	}

	/**
	 * Returns a cursored collection of user IDs for every user following the specified user.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFriendsIds($parameters = [])
	{
		return $this->get('friends/ids', $parameters);
	}

	/**
	 * Returns a cursored collection of user IDs for every user following the specified user.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFollowersIds($parameters = [])
	{
		return $this->get('followers/ids', $parameters);
	}

	/**
	 * Returns a collection of numeric IDs for every user who has a pending request to follow the authenticating user.
	 *
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsIn($parameters = [])
	{
		return $this->get('friendships/incoming', $parameters);
	}

	/**
	 * Returns a collection of numeric IDs for every protected user for whom the authenticating user has a pending follow request.
	 *
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsOut($parameters = [])
	{
		return $this->get('friendships/outgoing', $parameters);
	}

	/**
	 * Allows the authenticating users to follow the user specified in the ID parameter.
	 *
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - follow (0|1)
	 */
	public function postFollow($parameters = [])
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : screen_name or user_id');
		}

		return $this->post('friendships/create', $parameters);
	}

	/**
	 * Allows the authenticating user to unfollow the user specified in the ID parameter.
	 *
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postUnfollow($parameters = [])
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : screen_name or user_id');
		}

		return $this->post('friendships/destroy', $parameters);
	}

	/**
	 * Allows one to enable or disable retweets and device notifications from the specified user.
	 *
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - device (0|1)
	 * - retweets (0|1)
	 */
	public function postFollowUpdate($parameters = [])
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : screen_name or user_id');
		}

		return $this->post('friendships/update', $parameters);
	}

	/**
	 * Returns detailed information about the relationship between two arbitrary users.
	 *
	 * Parameters :
	 * - source_id
	 * - source_screen_name
	 * - target_id
	 * - target_screen_name
	 */
	public function getFriendships($parameters = [])
	{
		if (!array_key_exists('target_id', $parameters) && !array_key_exists('target_screen_name', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : target_id or target_screen_name');
		}

		return $this->get('friendships/show', $parameters);
	}

	/**
	 * Returns a cursored collection of user objects for every user the specified user is following (otherwise known as their “friends”).
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFriends($parameters = [])
	{
		return $this->get('friends/list', $parameters);
	}

	/**
	 * Returns a cursored collection of user objects for users following the specified user.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFollowers($parameters = [])
	{
		return $this->get('followers/list', $parameters);
	}

	/**
	 * Returns the relationships of the authenticating user to the comma-separated list of up to 100 screen_names or user_ids provided. Values for connections can be: following, following_requested, followed_by, none, blocking, muting.
	 *
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function getFriendshipsLookup($parameters = [])
	{
		return $this->get('friendships/lookup', $parameters);
	}

}
