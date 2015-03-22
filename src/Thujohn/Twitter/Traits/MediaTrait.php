<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait MediaTrait {

	/**
	 * Upload media (images) to Twitter, to use in a Tweet or Twitter-hosted Card.
	 *
	 * Parameters :
	 * - media
	 */
	public function uploadMedia($parameters = [])
	{
		if (!array_key_exists('media', $parameters))
		{
			throw new Exception('Parameter required missing : media');
		}

		return $this->post('media/upload', $parameters, true);
	}

}