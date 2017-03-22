<?php namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

Trait ListTrait {

	/**
	 * Returns all lists the authenticating or specified user subscribes to, including their own. The user is specified using the user_id or screen_name parameters. If no user is given, the authenticating user is used.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - reverse (0|1)
	 */
	public function getLists($parameters = [])
	{
		return $this->get('lists/list', $parameters);
	}

	/**
	 * Returns a timeline of tweets authored by members of the specified list. Retweets are included by default. Use the include_rts=false parameter to omit retweets.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - since_id
	 * - max_id
	 * - count
	 * - include_entities (0|1)
	 * - include_rts (0|1)
	 */
	public function getListStatuses($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		return $this->get('lists/statuses', $parameters);
	}

	/**
	 * Removes the specified member from the list. The authenticated user must be the list’s owner to remove members from the list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListMember($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/members/destroy', $parameters);
	}

	/**
	 * Returns the lists the specified user has been added to. If user_id or screen_name are not provided the memberships for the authenticating user are returned.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count
	 * - cursor
	 * - filter_to_owned_lists
	 */
	public function getListsMemberships($parameters = [])
	{
		return $this->get('lists/memberships', $parameters);
	}

	/**
	 * Returns the subscribers of the specified list. Private list subscribers will only be shown if the authenticated user owns the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - cursor
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListsSubscribers($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		return $this->get('lists/subscribers', $parameters);
	}

	/**
	 * Subscribes the authenticated user to the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function postListSubscriber($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/subscribers/create', $parameters);
	}

	/**
	 * Returns the subscribers of the specified list. Private list subscribers will only be shown if the authenticated user owns the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListSubscriber($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->get('lists/subscribers/show', $parameters);
	}

	/**
	 * Unsubscribes the authenticated user from the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListSubscriber($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		return $this->post('lists/subscribers/destroy', $parameters);
	}

	/**
	 * Adds multiple members to a list, by specifying a comma-separated list of member ids or screen names. The authenticated user must own the list to be able to add members to it. Note that lists can’t have more than 5,000 members, and you are limited to adding up to 100 members to a list at a time with this method.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function postListCreateAll($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		return $this->post('lists/members/create_all', $parameters);
	}

	/**
	 * Check if the specified user is a member of the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListMember($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : user_id or screen_name');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->get('lists/members/show', $parameters);
	}

	/**
	 * Returns the members of the specified list. Private list members will only be shown if the authenticated user owns the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - cursor
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListMembers($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->get('lists/members', $parameters);
	}

	/**
	 * Add a member to a list. The authenticated user must own the list to be able to add members to it. Note that lists cannot have more than 5,000 members.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - user_id
	 * - screen_name
	 */
	public function postListMember($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/members/create', $parameters);
	}

	/**
	 * Deletes the specified list. The authenticated user must own the list to be able to destroy it.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyList($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/destroy', $parameters);
	}

	/**
	 * Updates the specified list. The authenticated user must own the list to be able to update it.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 */
	public function postListUpdate($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/update', $parameters);
	}

	/**
	 * Creates a new list for the authenticated user. Note that you can’t create more than 20 lists per account.
	 *
	 * Parameters :
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 */
	public function postList($parameters = [])
	{
		if (!array_key_exists('name', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : name');
		}

		return $this->post('lists/create', $parameters);
	}

	/**
	 * Returns the specified list. Private lists will only be shown if the authenticated user owns the specified list.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function getList($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->get('lists/show', $parameters);
	}

	/**
	 * Obtain a collection of the lists the specified user is subscribed to, 20 lists per page by default. Does not include the user’s own lists.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListSubscriptions($parameters = [])
	{
		return $this->get('lists/subscriptions', $parameters);
	}

	/**
	 * Removes multiple members from a list, by specifying a comma-separated list of member ids or screen names. The authenticated user must own the list to be able to remove members from it. Note that lists can’t have more than 500 members, and you are limited to removing up to 100 members to a list at a time with this method.
	 *
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - user_id
	 * - screen_name
	 */
	public function destroyListMembers($parameters = [])
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new BadMethodCallException('Parameter required missing : list_id or slug');
		}

		if (array_key_exists('slug', $parameters) && (!array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters)))
		{
			throw new BadMethodCallException('Parameter required missing : owner_screen_name or owner_id');
		}

		return $this->post('lists/members/destroy_all', $parameters);
	}

	/**
	 * Returns the lists owned by the specified Twitter user. Private lists will only be shown if the authenticated user is also the owner of the lists.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListOwnerships($parameters = [])
	{
		return $this->get('lists/ownerships', $parameters);
	}

}
