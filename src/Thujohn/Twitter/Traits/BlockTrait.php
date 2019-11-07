<?php

namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

trait BlockTrait
{
    /**
     * Returns a collection of user objects that the authenticating user is blocking.
     *
     * Parameters :
     * - include_entities (0|1)
     * - skip_status (0|1)
     * - cursor
     */
    public function getBlocks($parameters = [])
    {
        return $this->get('blocks/list', $parameters);
    }

    /**
     * Returns an array of numeric user ids the authenticating user is blocking.
     *
     * Parameters :
     * - stringify_ids (0|1)
     * - cursor
     */
    public function getBlocksIds($parameters = [])
    {
        return $this->get('blocks/ids', $parameters);
    }

    /**
     * Blocks the specified user from following the authenticating user. In addition the blocked user will not show in the authenticating users mentions or timeline (unless retweeted by another user). If a follow or friend relationship exists it is destroyed.
     *
     * Parameters :
     * - screen_name
     * - user_id
     * - include_entities (0|1)
     * - skip_status (0|1)
     */
    public function postBlock($parameters = [])
    {
        if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : screen_name or user_id');
        }

        return $this->post('blocks/create', $parameters);
    }

    /**
     * Un-blocks the user specified in the ID parameter for the authenticating user. Returns the un-blocked user in the requested format when successful. If relationships existed before the block was instated, they will not be restored.
     *
     * Parameters :
     * - screen_name
     * - user_id
     * - include_entities (0|1)
     * - skip_status (0|1)
     */
    public function destroyBlock($parameters = [])
    {
        if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : screen_name or user_id');
        }

        return $this->post('blocks/destroy', $parameters);
    }
}
