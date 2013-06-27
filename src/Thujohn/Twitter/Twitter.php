<?php namespace Thujohn\Twitter;

use Config;

class Twitter {
	protected $twitter;
	protected $apiUrl;
	protected $consumerKey;
	protected $consumerSecret;
	protected $accessToken;
	protected $accessTokenSecret;

	public function __construct(){
		$this->apiUrl = Config::get('twitter::API_URL');
		$this->consumerKey = Config::get('twitter::CONSUMER_KEY');
		$this->consumerSecret = Config::get('twitter::CONSUMER_SECRET');
		$this->accessToken = Config::get('twitter::ACCESS_TOKEN');
		$this->accessTokenSecret = Config::get('twitter::ACCESS_TOKEN_SECRET');
	}

	public function query($name, $requestMethod = 'GET', $format = 'json', $parameters = array()){
		$baseUrl = $this->apiUrl.$name.'.'.$format;

		$oauth = array(
			'oauth_consumer_key' => $this->consumerKey,
			'oauth_token' => $this->accessToken,
			'oauth_nonce' => md5(microtime() . rand()),
			'oauth_timestamp' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version' => '1.0'
		);

		$oauth = array_map("rawurlencode", $oauth);
		$query = array_map("rawurlencode", $parameters);

		$arr = array_merge($oauth, $query);

		asort($arr);
		ksort($arr);

		$querystring = urldecode(http_build_query($arr, '', '&'));

		$base_string = mb_strtoupper($requestMethod)."&".rawurlencode($baseUrl)."&".rawurlencode($querystring);

		$key = rawurlencode($this->consumerSecret)."&".rawurlencode($this->accessTokenSecret);

		$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

		if (mb_strtoupper($requestMethod) == 'GET'){
			$baseUrl .= "?".http_build_query($query);
			$baseUrl = str_replace("&amp;","&",$baseUrl);
		}

		$oauth['oauth_signature'] = $signature;
		ksort($oauth);

		$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

		$options = array();
		$options[CURLOPT_HTTPHEADER] = array("Authorization: $auth");
		if (mb_strtoupper($requestMethod) == 'POST'){
			$options[CURLOPT_POSTFIELDS] = http_build_query($query);
			$options[CURLOPT_URL] = $baseUrl;
		}
		$options[CURLOPT_HEADER] = false;
		if (mb_strtoupper($requestMethod) == 'GET'){
			$options[CURLOPT_URL] = $baseUrl;
		}
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_SSL_VERIFYPEER] = false;

		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$response = curl_exec($feed);
		curl_close($feed);

		return $response;
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - include_rts (0|1)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - contributor_details (0|1)
	 * - include_entities (0|1)
	 */
	public function getMentionsTimeline($parameters = array()){
		$response = $this->query('statuses/mentions_timeline', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
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
	public function getUserTimeline($parameters = array()){
		$response = $this->query('statuses/user_timeline', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - exclude_replies (0|1)
	 * - contributor_details (0|1)
	 * - include_entities (0|1)
	 */
	public function getHomeTimeline($parameters = array()){
		$response = $this->query('statuses/home_timeline', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - trim_user (0|1)
	 * - include_entities (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getRtsTimeline($parameters = array()){
		$response = $this->query('statuses/retweets_of_me', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 */
	public function getRts($id, $parameters = array()){
		$response = $this->query('statuses/retweets/'.$id, 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 * - include_my_retweet (0|1)
	 * - include_entities (0|1)
	 */
	public function getTweet($id, $parameters = array()){
		$response = $this->query('statuses/show/'.$id, 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - status
	 * - in_reply_to_status_id
	 * - lat
	 * - long
	 * - place_id
	 * - display_coordinates (0|1)
	 * - trim_user (0|1)
	 */
	public function postTweet($parameters = array()){
		if (!array_key_exists('status', $parameters)){
			throw new \Exception('Parameter required missing : status');
		}

		$response = $this->query('statuses/update', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function destroyTweet($id, $parameters = array()){
		$response = $this->query('statuses/destroy/'.$id, 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function postRt($id, $parameters = array()){
		$response = $this->query('statuses/retweet/'.$id, 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
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
	public function postTweetMedia($parameters = array()){
		if (!array_key_exists('status', $parameters) || !array_key_exists('media[]', $parameters)){
			throw new \Exception('Parameter required missing : status or media[]');
		}

		$response = $this->query('statuses/update_with_media', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
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
	public function getOembed($parameters = array()){
		if (!array_key_exists('id', $parameters) || !array_key_exists('url', $parameters)){
			throw new \Exception('Parameter required missing : id or url');
		}

		$response = $this->query('statuses/oembed', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getRters($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('statuses/retweeters/ids', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
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
	 */
	public function getSearch($parameters = array()){
		if (!array_key_exists('q', $parameters)){
			throw new \Exception('Parameter required missing : q');
		}

		$response = $this->query('search/tweets', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getDmsIn($parameters = array()){
		$response = $this->query('direct_messages', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - page
	 * - include_entities (0|1)
	 */
	public function getDmsOut($parameters = array()){
		$response = $this->query('direct_messages/sent', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 */
	public function getDm($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('direct_messages/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities
	 */
	public function destroyDm($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('direct_messages/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - text
	 */
	public function postDm($parameters = array()){
		if (!array_key_exists('user_id', $parameters) || !array_key_exists('screen_name', $parameters) || !array_key_exists('text', $parameters)){
			throw new \Exception('Parameter required missing : user_id, screen_name or text');
		}

		$response = $this->query('direct_messages/new', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 */
	public function getNoRters($parameters = array()){
		$response = $this->query('friendships/no_retweets/ids', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFriendsIds($parameters = array()){
		$response = $this->query('friends/ids', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFollowersIds($parameters = array()){
		$response = $this->query('followers/ids', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function getFriendshipsLookup($parameters = array()){
		$response = $this->query('friendships/lookup', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsIn($parameters = array()){
		$response = $this->query('friendships/incoming', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsOut($parameters = array()){
		$response = $this->query('friendships/outgoing', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - follow (0|1)
	 */
	public function postFollow($parameters = array()){
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters)){
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		$response = $this->query('friendships/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postUnfollow($parameters = array()){
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters)){
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		$response = $this->query('friendships/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - device (0|1)
	 * - retweets (0|1)
	 */
	public function postFollowUpdate($parameters = array()){
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters)){
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		$response = $this->query('friendships/update', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - source_id
	 * - source_screen_name
	 * - target_id
	 * - target_screen_name
	 */
	public function getFriendships($parameters = array()){
		$response = $this->query('friendships/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFriends($parameters = array()){
		$response = $this->query('friends/list', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFollowers($parameters = array()){
		$response = $this->query('followers/list', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	public function getSettings(){
		$response = $this->query('account/settings', 'GET', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - trend_location_woeid
	 * - sleep_time_enabled (0|1)
	 * - start_sleep_time
	 * - end_sleep_time
	 * - time_zone
	 * - lang
	 */
	public function postSettings($parameters = array()){
		if (empty($parameters)){
			throw new \Exception('Parameter missing');
		}

		$response = $this->query('account/settings', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - device (sms|none)
	 * - include_entities (0|1)
	 */
	public function postSettingsDevice($parameters = array()){
		if (!array_key_exists('device', $parameters)){
			throw new \Exception('Parameter required missing : device');
		}

		$response = $this->query('account/update_delivery_device', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - name
	 * - url
	 * - location
	 * - description (0-160)
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postProfile($parameters = array()){
		if (empty($parameters)){
			throw new \Exception('Parameter missing');
		}

		$response = $this->query('account/update_profile', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - image
	 * - tile
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - use (0|1)
	 */
	public function postBackground($parameters = array()){
		if (!array_key_exists('image', $parameters) || !array_key_exists('tile', $parameters) || !array_key_exists('use', $parameters)){
			throw new \Exception('Parameter required missing : image, tile or use');
		}

		$response = $this->query('account/update_profile_background_image', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - profile_background_color
	 * - profile_link_color
	 * - profile_sidebar_border_color
	 * - profile_sidebar_fill_color
	 * - profile_text_color
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postColors($parameters = array()){
		if (empty($parameters)){
			throw new \Exception('Parameter missing');
		}

		$response = $this->query('account/update_profile_colors', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - image
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postProfileImage($parameters = array()){
		if (!array_key_exists('image', $parameters)){
			throw new \Exception('Parameter required missing : image');
		}

		$response = $this->query('account/update_profile_image', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getCredentials($parameters = array()){
		$response = $this->query('account/verify_credentials', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - cursor
	 */
	public function getBlocks($parameters = array()){
		$response = $this->query('blocks/list', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 * - cursor
	 */
	public function getBlocksIds($parameters = array()){
		$response = $this->query('blocks/ids', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postBlock($parameters = array()){
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters)){
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		$response = $this->query('blocks/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function destroyBlock($parameters = array()){
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters)){
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		$response = $this->query('blocks/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsers($parameters = array()){
		if (!array_key_exists('id', $parameters) || !array_key_exists('screen_name', $parameters)){
			throw new \Exception('Parameter required missing : id or screen_name');
		}

		$response = $this->query('users/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - q
	 * - page
	 * - count
	 * - include_entities (0|1)
	 */
	public function getUsersSearch($parameters = array()){
		if (!array_key_exists('q', $parameters)){
			throw new \Exception('Parameter required missing : q');
		}

		$response = $this->query('users/search', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributees($parameters = array()){
		$response = $this->query('users/contributees', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributors($parameters = array()){
		$response = $this->query('users/contributors', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	public function destroyUserBanner(){
		$response = $this->query('account/remove_profile_banner', 'POST', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - banner
	 * - width
	 * - height
	 * - offset_left
	 * - offset_top
	 */
	public function postUserBanner($parameters = array()){
		if (!array_key_exists('banner', $parameters)){
			throw new \Exception('Parameter required missing : banner');
		}

		$response = $this->query('account/update_profile_banner', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 */
	public function getUserBanner($parameters = array()){
		$response = $this->query('users/profile_banner', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggesteds($slug, $parameters = array()){
		$response = $this->query('users/suggestions/'.$slug, 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggestions($parameters = array()){
		$response = $this->query('users/suggestions', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	public function getSuggestedsMembers($slug){
		$response = $this->query('users/suggestions/'.$slug.'/members', 'GET', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-200)
	 * - since_id
	 * - max_id
	 * - include_entities (0|1)
	 */
	public function getFavorites($parameters = array()){
		$response = $this->query('favorites/list', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function destroyFavorite($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('favorites/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function postFavorite($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('favorites/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - reverse (0|1)
	 */
	public function getLists($parameters = array()){
		$response = $this->query('lists/list', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - since_id
	 * - max_id
	 * - count
	 * - include_entities (0|1)
	 * - include_rts (0|1)
	 */
	public function getListsStatuses($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/statuses', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListMember($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('owner_screen_name', $parameters) || !array_key_exists('owner_id', $parameters)){
			throw new \Exception('Parameter required missing : list_id, slug, owner_screen_name or owner_id');
		}

		$response = $this->query('lists/members/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - cursor
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListsSubscribers($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/subscribers', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function postListSubscriber($parameters = array()){
		if (!array_key_exists('owner_screen_name', $parameters) || !array_key_exists('owner_id', $parameters) || !array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : owner_screen_name, owner_id, list_id or slug');
		}

		$response = $this->query('lists/subscribers/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListSubscriber($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('user_id', $parameters) || !array_key_exists('screen_name', $parameters)){
			throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
		}

		$response = $this->query('lists/subscribers/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListSubscriber($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/subscribers/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function postListCreateAll($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/members/create_all', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListMember($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('user_id', $parameters) || !array_key_exists('screen_name', $parameters)){
			throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
		}

		$response = $this->query('lists/members/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 * - cursor
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getListMembers($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/members', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function postListMember($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('user_id', $parameters) || !array_key_exists('screen_name', $parameters)){
			throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
		}

		$response = $this->query('lists/members/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function destroyList($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/destroy', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 * - owner_screen_name
	 * - owner_id
	 */
	public function postListUpdate($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/update', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 */
	public function postList($parameters = array()){
		if (!array_key_exists('name', $parameters)){
			throw new \Exception('Parameter required missing : name');
		}

		$response = $this->query('lists/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function getList($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/show', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListSubscriptions($parameters = array()){
		$response = $this->query('lists/subscriptions', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - user_id
	 * - screen_name
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListMembers($parameters = array()){
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters)){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		$response = $this->query('lists/members/destroy_all', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListOwnerships($parameters = array()){
		$response = $this->query('lists/ownerships', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	public function getSavedSearches(){
		$response = $this->query('saved_searches/list', 'GET', 'json');

		return json_decode($response);
	}

	public function getSavedSearch($id){
		$response = $this->query('saved_searches/show/'.$id, 'GET', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - query
	 */
	public function postSavedSearch($parameters = array()){
		if (!array_key_exists('query', $parameters)){
			throw new \Exception('Parameter required missing : query');
		}

		$response = $this->query('saved_searches/create', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	public function destroySavedSearch($id, $parameters = array()){
		$response = $this->query('saved_searches/destroy/'.$id, 'POST', 'json', $parameters);

		return json_decode($response);
	}

	public function getGeo($id){
		$response = $this->query('geo/id/'.$id, 'GET', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 * - accuracy
	 * - granularity (poi|neighborhood|city|admin|country)
	 * - max_results
	 * - callback
	 */
	public function getGeoReverse($parameters = array()){
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters)){
			throw new \Exception('Parameter required missing : lat or long');
		}

		$response = $this->query('geo/reverse_geocode', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 * - query
	 * - ip
	 * - granularity (poi|neighborhood|city|admin|country)
	 * - accuracy
	 * - max_results
	 * - contained_within
	 * - attribute:street_address
	 * - callback
	 */
	public function getGeoSearch($parameters = array()){
		$response = $this->query('geo/search', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 * - name
	 * - contained_within
	 * - attribute:street_address
	 * - callback
	 */
	public function getGeoSimilar($parameters = array()){
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters) || !array_key_exists('name', $parameters)){
			throw new \Exception('Parameter required missing : lat, long or name');
		}

		$response = $this->query('geo/similar_places', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - name
	 * - contained_within
	 * - token
	 * - lat
	 * - long
	 * - attribute:street_address
	 * - callback
	 */
	public function postGeo($parameters = array()){
		if (!array_key_exists('name', $parameters) || !array_key_exists('contained_within', $parameters) || !array_key_exists('token', $parameters) || !array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters)){
			throw new \Exception('Parameter required missing : name, contained_within, token, lat or long');
		}

		$response = $this->query('geo/place', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - id
	 * - exclude
	 */
	public function getTrendsPlace($parameters = array()){
		if (!array_key_exists('id', $parameters)){
			throw new \Exception('Parameter required missing : id');
		}

		$response = $this->query('trends/place', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	public function getTrendsAvailable(){
		$response = $this->query('trends/available', 'GET', 'json');

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 */
	public function getTrendsClosest($parameters = array()){
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters)){
			throw new \Exception('Parameter required missing : lat, long or name');
		}

		$response = $this->query('trends/closest', 'GET', 'json', $parameters);

		return json_decode($response);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postSpam($parameters = array()){
		if (empty($parameters)){
			throw new \Exception('Parameter missing');
		}

		$response = $this->query('users/report_spam', 'POST', 'json', $parameters);

		return json_decode($response);
	}

	public function getHelpConfiguration(){
		$response = $this->query('help/configuration', 'GET', 'json');

		return json_decode($response);
	}

	public function getHelpLanguages(){
		$response = $this->query('help/languages', 'GET', 'json');

		return json_decode($response);
	}

	public function getHelpPrivacy(){
		$response = $this->query('help/privacy', 'GET', 'json');

		return json_decode($response);
	}

	public function getHelpTos(){
		$response = $this->query('help/tos', 'GET', 'json');

		return json_decode($response);
	}

	public function getAppRateLimit(){
		$response = $this->query('application/rate_limit_status', 'GET', 'json');

		return json_decode($response);
	}
}