<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait StatusTrait {

	/**
	 * Returns the 20 most recent mentions (tweets containing a users’s @screen_name) for the authenticating user.
	 *
	 * Parameters :
	 * - count (1-200)
	 * - include_rts (0|1)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - contributor_details (0|1)
	 * - include_entities (0|1)
	 */
	public function getMentionsTimeline($parameters = [])
	{
		return $this->get('statuses/mentions_timeline', $parameters);
	}

	/**
	 * Returns a collection of the most recent Tweets posted by the user indicated by the screen_name or user_id parameters.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - since_id
	 * - count (1-200)
	 * - include_rts (0|1)
	 * - max_id
	 * - trim_user (0|1)
	 * - exclude_replies (0|1)
	 * - contributor_details (0|1)
	 * - include_entities (0|1)
	 */
	public function getUserTimeline($parameters = [])
	{
		return $this->get('statuses/user_timeline', $parameters);
	}

	/**
	 * Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow. The home timeline is central to how most users interact with the Twitter service.
	 *
	 * Parameters :
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - exclude_replies (0|1)
	 * - contributor_details (0|1)
	 * - include_entities (0|1)
	 */
	public function getHomeTimeline($parameters = [])
	{
		return $this->get('statuses/home_timeline', $parameters);
	}

	/**
	 * Returns the most recent tweets authored by the authenticating user that have been retweeted by others.
	 *
	 * Parameters :
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - include_entities (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getRtsTimeline($parameters = [])
	{
		return $this->get('statuses/retweets_of_me', $parameters);
	}

	/**
	 * Returns a collection of the 100 most recent retweets of the tweet specified by the id parameter.
	 *
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 */
	public function getRts($id, $parameters = [])
	{
		return $this->get('statuses/retweets/'.$id, $parameters);
	}

	/**
	 * Returns a single Tweet, specified by the id parameter. The Tweet’s author will also be embedded within the tweet.
	 *
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 * - include_my_retweet (0|1)
	 * - include_entities (0|1)
	 */
	public function getTweet($id, $parameters = [])
	{
		return $this->get('statuses/show/'.$id, $parameters);
	}

	/**
	 * Destroys the status specified by the required ID parameter. The authenticating user must be the author of the specified status. Returns the destroyed status if successful.
	 *
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function destroyTweet($id, $parameters = [])
	{
		return $this->post('statuses/destroy/'.$id, $parameters);
	}

	/**
	 * Updates the authenticating user’s current status, also known as tweeting.
	 *
	 * Parameters :
	 * - status
	 * - in_reply_to_status_id
	 * - lat
	 * - long
	 * - place_id
	 * - display_coordinates (0|1)
	 * - trim_user (0|1)
	 * - media_ids
	 */
	public function postTweet($parameters = [])
	{
		if (!array_key_exists('status', $parameters))
		{
			throw new Exception('Parameter required missing : status');
		}

		return $this->post('statuses/update', $parameters);
	}

	/**
	 * Retweets a tweet. Returns the original tweet with retweet details embedded.
	 *
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function postRt($id, $parameters = [])
	{
		return $this->post('statuses/retweet/'.$id, $parameters);
	}

	/**
	 * Updates the authenticating user’s current status and attaches media for upload. In other words, it creates a Tweet with a picture attached.
	 * DEPRECATED
	 *
	 * Parameters :
	 * - status
	 * - media[]
	 * - possibly_sensitive
	 * - in_reply_to_status_id
	 * - lat
	 * - long
	 * - place_id
	 * - display_coordinates (0|1)
	 */
	public function postTweetMedia($parameters = [])
	{
		if (!array_key_exists('status', $parameters) || !array_key_exists('media[]', $parameters))
		{
			throw new Exception('Parameter required missing : status or media[]');
		}

		return $this->post('statuses/update_with_media', $parameters, true);
	}

	/**
	 * Returns a single Tweet, specified by either a Tweet web URL or the Tweet ID, in an oEmbed-compatible format. The returned HTML snippet will be automatically recognized as an Embedded Tweet when Twitter’s widget JavaScript is included on the page.
	 *
	 * Parameters :
	 * - id
	 * - url
	 * - maxwidth (250-550)
	 * - hide_thread (0|1)
	 * - omit_script (0|1)
	 * - align (left|right|center|none)
	 * - related (twitterapi|twittermedia|twitter)
	 * - lang
	 */
	public function getOembed($parameters = [])
	{
		if (!array_key_exists('id', $parameters) && !array_key_exists('url', $parameters))
		{
			throw new Exception('Parameter required missing : id or url');
		}

		return $this->get('statuses/oembed', $parameters);
	}

	/**
	 * Returns a collection of up to 100 user IDs belonging to users who have retweeted the tweet specified by the id parameter.
	 *
	 * Parameters :
	 * - id
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getRters($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new Exception('Parameter required missing : id');
		}

		return $this->get('statuses/retweeters/ids', $parameters);
	}

	/**
	 * Returns fully-hydrated tweet objects for up to 100 tweets per request, as specified by comma-separated values passed to the id parameter.
	 *
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 * - trim_user (0|1)
	 * - map (0|1)
	 */
	public function getStatusesLookup($parameters = [])
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new Exception('Parameter required missing : id');
		}

		return $this->get('statuses/lookup', $parameters);
	}

}