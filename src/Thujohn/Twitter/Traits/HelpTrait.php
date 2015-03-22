<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait HelpTrait {

	/**
	 * Report the specified user as a spam account to Twitter. Additionally performs the equivalent of POST blocks / create on behalf of the authenticated user.
	 *
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postSpam($parameters = [])
	{
		if (empty($parameters))
		{
			throw new Exception('Parameter missing : screen_name or user_id');
		}

		return $this->post('users/report_spam', $parameters);
	}

	/**
	 * Returns the current configuration used by Twitter including twitter.com slugs which are not usernames, maximum photo resolutions, and t.co URL lengths.
	 */
	public function getHelpConfiguration($parameters = [])
	{
		return $this->get('help/configuration', $parameters);
	}

	/**
	 * Returns the list of languages supported by Twitter along with the language code supported by Twitter.
	 */
	public function getHelpLanguages($parameters = [])
	{
		return $this->get('help/languages', $parameters);
	}

	/**
	 * Returns Twitter’s Privacy Policy.
	 */
	public function getHelpPrivacy($parameters = [])
	{
		return $this->get('help/privacy', $parameters);
	}

	/**
	 * Returns the Twitter Terms of Service. Note: these are not the same as the Developer Policy.
	 */
	public function getHelpTos($parameters = [])
	{
		return $this->get('help/tos', $parameters);
	}

	/**
	 * Returns the current rate limits for methods belonging to the specified resource families.
	 */
	public function getAppRateLimit($parameters = [])
	{
		return $this->get('application/rate_limit_status', $parameters);
	}

}