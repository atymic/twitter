<?php

namespace Thujohn\Twitter\Traits;

trait AccountActivityTrait
{
    /**
     * Creates HMAC SHA-256 hash from incomming crc_token and consumer secret.
     * This base64 encoded hash needs to be returned by the application when Twitter calls the webhook.
     *
     * @param mixed $crcToken
     *
     * @return string
     */
    public function crcHash($crcToken)
    {
        $hash = hash_hmac('sha256', $crcToken, $this->tconfig['CONSUMER_SECRET'], true);

        return 'sha256='.base64_encode($hash);
    }

    /**
     * Registers a webhook $url for all event types in the given environment.
     *
     * @param mixed $env
     * @param mixed $url
     *
     * @return object
     */
    public function setWebhook($env, $url)
    {
        return $this->post("account_activity/all/{$env}/webhooks", ['url' => $url]);
    }

    /**
     * Returns webhook URLs for the given environment (or all environments if none provided), and their statuses for the authenticating app.
     *
     * @param mixed $env
     *
     * @return object
     */
    public function getWebhooks($env = null)
    {
        return $this->get('account_activity/all/'.($env ? $env.'/' : '').'webhooks');
    }

    /**
     * Triggers the challenge response check (CRC) for the given enviroments webhook for all activites.
     * If the check is successful, returns 204 and reenables the webhook by setting its status to valid.
     *
     * @param mixed $env
     * @param mixed $webhookId
     *
     * @return bool
     */
    public function updateWebhooks($env, $webhookId)
    {
        $this->query("account_activity/all/{$env}/webhooks/{$webhookId}", 'PUT');

        return $this->response['code'] === 204;
    }

    /**
     * Removes the webhook from the provided application's all activities configuration.
     * The webhook ID can be accessed by making a call to GET /1.1/account_activity/all/webhooks (getWebhooks).
     *
     * @param mixed $env
     * @param mixed $webhookId
     *
     * @return bool
     */
    public function destroyWebhook($env, $webhookId)
    {
        $this->delete("account_activity/all/{$env}/webhooks/{$webhookId}");

        return $this->response['code'] === 204;
    }

    /**
     * Subscribes the provided application to all events for the provided environment for all message types.
     * Returns HTTP 204 on success.
     * After activation, all events for the requesting user will be sent to the application’s webhook via POST request.
     *
     * @param mixed $env
     *
     * @return bool
     */
    public function setSubscriptions($env)
    {
        $this->post("account_activity/all/{$env}/subscriptions");

        return $this->response['code'] === 204;
    }

    /**
     * Provides a way to determine if a webhook configuration is subscribed to the provided user’s events.
     * If the provided user context has an active subscription with provided application, returns 204 OK.
     * If the response code is not 204, then the user does not have an active subscription.
     * See HTTP Response code and error messages for details:
     * https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-subscriptions.
     *
     * @param mixed $env
     *
     * @return bool
     */
    public function getSubscriptions($env)
    {
        $this->get("account_activity/all/{$env}/subscriptions");

        return $this->response['code'] === 204;
    }

    /**
     * Returns the count of subscriptions that are currently active on your account for all activities.
     *
     * @return object
     */
    public function getSubscriptionsCount()
    {
        return $this->get('account_activity/all/subscriptions/count', [], false, 'json', true);
    }

    /**
     * Returns a list of the current All Activity type subscriptions.
     *
     * @param mixed $env
     *
     * @return object
     */
    public function getSubscriptionsList($env)
    {
        return $this->get("account_activity/all/{$env}/subscriptions/list", [], false, 'json', true);
    }

    /**
     * Deactivates subscription for the specified user id from the environment.
     * After deactivation, all events for the requesting user will no longer be sent to the webhook URL.
     *
     * @param mixed $env
     * @param mixed $userId
     *
     * @return bool
     */
    public function destroyUserSubscriptions($env, $userId)
    {
        $this->delete("account_activity/all/{$env}/subscriptions/{$userId}", [], false, 'json', true);

        return $this->response['code'] === 204;
    }
}
