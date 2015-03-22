<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait DirectMessageTrait {

	/**
	 * Returns the 20 most recent direct messages sent by the authenticating user. Includes detailed information about the sender and recipient user. You can request up to 200 direct messages per call, up to a maximum of 800 outgoing DMs.
	 *
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - page
	 * - include_entities (0|1)
	 */
	public function getDmsOut($parameters = [])
	{
		return $this->get('direct_messages/sent', $parameters);
	}

	/**
	 * Returns a single direct message, specified by an id parameter. Like the /1.1/direct_messages.format request, this method will include the user objects of the sender and recipient.
	 *
	 * Parameters :
	 * - id
	 */
	public function getDm($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new Exception('Parameter required missing : id');
		}

		return $this->get('direct_messages/show', $parameters);
	}

	/**
	 * Returns the 20 most recent direct messages sent to the authenticating user. Includes detailed information about the sender and recipient user. You can request up to 200 direct messages per call, and only the most recent 200 DMs will be available using this endpoint.
	 *
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getDmsIn($parameters = [])
	{
		return $this->get('direct_messages', $parameters);
	}

	/**
	 * Destroys the direct message specified in the required ID parameter. The authenticating user must be the recipient of the specified direct message.
	 *
	 * Parameters :
	 * - id
	 * - include_entities
	 */
	public function destroyDm($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new Exception('Parameter required missing : id');
		}

		return $this->post('direct_messages/destroy', $parameters);
	}

	/**
	 * Sends a new direct message to the specified user from the authenticating user. Requires both the user and text parameters and must be a POST. Returns the sent message in the requested format if successful.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - text
	 */
	public function postDm($parameters = [])
	{
		if ((!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters)) || !array_key_exists('text', $parameters))
		{
			throw new Exception('Parameter required missing : user_id, screen_name or text');
		}

		return $this->post('direct_messages/new', $parameters);
	}

}