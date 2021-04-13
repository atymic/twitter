<?php

namespace Atymic\Twitter\ApiV1\Traits;

use BadMethodCallException;

trait HelpTrait
{
    /**
     * Report the specified user as a spam account to Twitter. Additionally performs the equivalent of POST blocks / create on behalf of the authenticated user.
     *
     * Parameters :
     * - screen_name
     * - user_id
     *
     * @param mixed $parameters
     */
    public function postSpam($parameters = [])
    {
        if (empty($parameters)) {
            throw new BadMethodCallException('Parameter missing : screen_name or user_id');
        }

        return $this->post('users/report_spam', $parameters);
    }

    /**
     * Returns the current configuration used by Twitter including twitter.com slugs which are not usernames, maximum photo resolutions, and t.co URL lengths.
     *
     * @param mixed $parameters
     */
    public function getHelpConfiguration($parameters = [])
    {
        return $this->get('help/configuration', $parameters);
    }

    /**
     * Returns the list of languages supported by Twitter along with the language code supported by Twitter.
     *
     * @param mixed $parameters
     */
    public function getHelpLanguages($parameters = [])
    {
        return $this->get('help/languages', $parameters);
    }

    /**
     * Returns Twitter’s Privacy Policy.
     *
     * @param mixed $parameters
     */
    public function getHelpPrivacy($parameters = [])
    {
        return $this->get('help/privacy', $parameters);
    }

    /**
     * Returns the Twitter Terms of Service. Note: these are not the same as the Developer Policy.
     *
     * @param mixed $parameters
     */
    public function getHelpTos($parameters = [])
    {
        return $this->get('help/tos', $parameters);
    }

    /**
     * Returns the current rate limits for methods belonging to the specified resource families.
     *
     * @param mixed $parameters
     */
    public function getAppRateLimit($parameters = [])
    {
        return $this->get('application/rate_limit_status', $parameters);
    }
}
