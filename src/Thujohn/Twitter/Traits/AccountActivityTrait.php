<?php

namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

trait AccountActivityTrait
{
	/**
	 * Creates HMAC SHA-256 hash from incomming crc_token and consumer secret.
	 *
	 * @param  mixed $crcToken
	 *
	 * @return void
	 */
	public function CrcHash($crcToken)
	{
		$hash = hash_hmac('sha256', $crcToken, $this->tconfig['CONSUMER_SECRET'], true);

		return 'sha256='.base64_encode($hash);
	}
	
	/**
	 * Registers a webhook $url for all event types.
	 *
	 * @param  mixed $env
	 * @param  mixed $url
	 *
	 * @return void
	 */
	public function setAccountWebhook($env = null, $url)
	{
		return $this->post('account_activity/all/'.($env ? $env.'/' : ''). 'webhooks', ['url' => $url]);
	}

	/**
	 * Returns webhook URLs for the given environment (or all, if none provided) and their statuses for the authenticating app. 
	 *
	 * @param  mixed $env
	 *
	 * @return void
	 */
	public function getAccountWebhook($env = null)
	{
		return $this->get('account_activity/all/'.($env ? $env.'/' : ''). 'webhooks');
	}

	/**
	 * Subscribes the provided application to all events for the provided environment for all message types. 
	 * After activation, all events for the requesting user will be sent to the application’s webhook via POST request.
	 *
	 * @param  mixed $env
	 *
	 * @return void
	 */
	public function setSubscriptions($env = null)
	{
		return $this->post('account_activity/all/'.($env ? $env.'/' : ''). 'subscriptions');
	}

}
