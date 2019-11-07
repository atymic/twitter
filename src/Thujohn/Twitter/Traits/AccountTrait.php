<?php

namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

trait AccountTrait
{
    /**
     * Returns settings (including current trend, geo and sleep time information) for the authenticating user.
     */
    public function getSettings($parameters = [])
    {
        return $this->get('account/settings', $parameters);
    }

    /**
     * Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful; returns a 401 status code and an error message if not. Use this method to test if supplied user credentials are valid.
     *
     * Parameters :
     * - include_entities (0|1)
     * - skip_status (0|1)
     */
    public function getCredentials($parameters = [])
    {
        return $this->get('account/verify_credentials', $parameters);
    }

    /**
     * Updates the authenticating user’s settings.
     *
     * Parameters :
     * - trend_location_woeid
     * - sleep_time_enabled (0|1)
     * - start_sleep_time
     * - end_sleep_time
     * - time_zone
     * - lang
     */
    public function postSettings($parameters = [])
    {
        if (empty($parameters)) {
            throw new BadMethodCallException('Parameter missing');
        }

        return $this->post('account/settings', $parameters);
    }

    /**
     * Sets which device Twitter delivers updates to for the authenticating user. Sending none as the device parameter will disable SMS updates.
     *
     * Parameters :
     * - device (sms|none)
     * - include_entities (0|1)
     */
    public function postSettingsDevice($parameters = [])
    {
        if (!array_key_exists('device', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : device');
        }

        return $this->post('account/update_delivery_device', $parameters);
    }

    /**
     * Sets some values that users are able to set under the “Account” tab of their settings page. Only the parameters specified will be updated.
     *
     * Parameters :
     * - name
     * - url
     * - location
     * - description (0-160)
     * - include_entities (0|1)
     * - skip_status (0|1)
     */
    public function postProfile($parameters = [])
    {
        if (empty($parameters)) {
            throw new BadMethodCallException('Parameter missing');
        }

        return $this->post('account/update_profile', $parameters);
    }

    /**
     * Updates the authenticating user’s profile background image. This method can also be used to enable or disable the profile background image.
     *
     * Parameters :
     * - image
     * - tile
     * - include_entities (0|1)
     * - skip_status (0|1)
     * - use (0|1)
     */
    public function postBackground($parameters = [])
    {
        if (!array_key_exists('image', $parameters) && !array_key_exists('tile', $parameters) && !array_key_exists('use', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : image, tile or use');
        }

        return $this->post('account/update_profile_background_image', $parameters, true);
    }

    /**
     * Updates the authenticating user’s profile image. Note that this method expects raw multipart data, not a URL to an image.
     *
     * Parameters :
     * - image
     * - include_entities (0|1)
     * - skip_status (0|1)
     */
    public function postProfileImage($parameters = [])
    {
        if (!array_key_exists('image', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : image');
        }

        return $this->post('account/update_profile_image', $parameters, false);
    }

    /**
     * Removes the uploaded profile banner for the authenticating user. Returns HTTP 200 upon success.
     */
    public function destroyUserBanner($parameters = [])
    {
        return $this->post('account/remove_profile_banner', $parameters);
    }

    /**
     * Uploads a profile banner on behalf of the authenticating user. For best results, upload an profile_banner_url node in their Users objects. More information about sizing variations can be found in User Profile Images and Banners and GET users / profile_banner.
     *
     * Parameters :
     * - banner
     * - width
     * - height
     * - offset_left
     * - offset_top
     */
    public function postUserBanner($parameters = [])
    {
        if (!array_key_exists('banner', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : banner');
        }

        return $this->post('account/update_profile_banner', $parameters);
    }
}
