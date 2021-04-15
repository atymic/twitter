<?php

namespace Atymic\Twitter\ApiV1\Traits;

use Atymic\Twitter\ApiV1\Service\Twitter;
use BadMethodCallException;

trait DirectMessageTrait
{
    /**
     * Returns a single direct message event, specified by an id parameter.
     *
     * Parameters :
     * - id
     *
     * @param mixed $parameters
     */
    public function getDm($parameters = [])
    {
        if (!array_key_exists('id', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : id');
        }

        return $this->get('direct_messages/events/show', $parameters);
    }

    /**
     * Returns all Direct Message events (both sent and received) within the last 30 days. Sorted in reverse-chronological order.
     *
     * Parameters :
     * - count (1-50)
     * - cursor
     *
     * @param mixed $parameters
     */
    public function getDms($parameters = [])
    {
        return $this->get('direct_messages/events/list', $parameters);
    }

    /**
     * Destroys the direct message specified in the required ID parameter. The authenticating user must be the recipient of the specified direct message.
     *
     * Parameters :
     * - id
     *
     * @param mixed $parameters
     */
    public function destroyDm($parameters = [])
    {
        if (!array_key_exists('id', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : id');
        }

        return $this->delete('direct_messages/events/destroy', $parameters);
    }

    /**
     * Publishes a new message_create event resulting in a Direct Message sent to a specified user from the
     * authenticating user. Returns an event if successful. Supports publishing Direct Messages with optional Quick
     * Reply and media attachment.
     *
     * Parameters :
     * - type
     * - message_create
     *
     * @see https://developer.twitter.com/en/docs/direct-messages/sending-and-receiving/api-reference/new-event
     *
     * @param mixed $parameters
     *
     * @throws BadMethodCallException
     */
    public function postDm($parameters = [])
    {
        $apiReference = 'https://developer.twitter.com/en/docs/direct-messages/sending-and-receiving/api-reference/new-event';

        if (!array_key_exists('event', $parameters)) {
            throw new BadMethodCallException(sprintf('Missing required parameter: `event`. See %s', $apiReference));
        }

        $parameters[Twitter::KEY_REQUEST_FORMAT] = Twitter::REQUEST_FORMAT_JSON;

        return $this->post('direct_messages/events/new', $parameters);
    }
}
