<?php

namespace Atymic\Twitter\ApiV1\Traits;

use BadMethodCallException;

trait SearchTrait
{
    /**
     * Returns a collection of relevant Tweets matching a specified query.
     *
     * Parameters :
     * - q
     * - geocode
     * - lang
     * - locale
     * - result_type (mixed|recent|popular)
     * - count (1-100)
     * - until (YYYY-MM-DD)
     * - since_id
     * - max_id
     * - include_entities (0|1)
     * - callback
     *
     * @param mixed $parameters
     */
    public function getSearch($parameters = [])
    {
        if (!array_key_exists('q', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : q');
        }

        return $this->get('search/tweets', $parameters);
    }

    /**
     * Returns the authenticated user’s saved search queries.
     */
    public function getSavedSearches()
    {
        return $this->get('saved_searches/list');
    }

    /**
     * Retrieve the information for the saved search represented by the given id. The authenticating user must be the owner of saved search ID being requested.
     *
     * @param mixed $id
     */
    public function getSavedSearch($id)
    {
        return $this->get('saved_searches/show/' . $id);
    }

    /**
     * Create a new saved search for the authenticated user. A user may only have 25 saved searches.
     *
     * Parameters :
     * - query
     *
     * @param mixed $parameters
     */
    public function postSavedSearch($parameters = [])
    {
        if (!array_key_exists('query', $parameters)) {
            throw new BadMethodCallException('Parameter required missing : query');
        }

        return $this->post('saved_searches/create', $parameters);
    }

    /**
     * Destroys a saved search for the authenticating user. The authenticating user must be the owner of saved search id being destroyed.
     *
     * @param mixed $id
     * @param mixed $parameters
     */
    public function destroySavedSearch($id, $parameters = [])
    {
        return $this->post('saved_searches/destroy/' . $id, $parameters);
    }
}
