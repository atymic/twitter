<?php namespace Thujohn\Twitter;

use Config;
use Carbon\Carbon as Carbon;
use tmhOAuth;
use Session;

class Twitter extends tmhOAuth {

	const METHOD_POST	= 'POST';
	const METHOD_GET	= 'GET';

	/**
	 * Store the default config values for the class
	 */
	private $default;

	public function __construct($config = array())
	{
		$this->default = array();

		$this->default['consumer_key']    = Config::get('thujohn/twitter::CONSUMER_KEY');
		$this->default['consumer_secret'] = Config::get('thujohn/twitter::CONSUMER_SECRET');
		$this->default['token']           = Config::get('thujohn/twitter::ACCESS_TOKEN');
		$this->default['secret']          = Config::get('thujohn/twitter::ACCESS_TOKEN_SECRET');

		if (Session::has('access_token'))
		{
			$access_token = Session::get('access_token');

			if (!empty($access_token['oauth_token']) && !empty($access_token['oauth_token_secret']))
			{
				$this->default['token']  = $access_token['oauth_token'];
				$this->default['secret'] = $access_token['oauth_token_secret'];
			}
		}
		$this->default['use_ssl']    = Config::get('thujohn/twitter::USE_SSL');
		$this->default['user_agent'] = 'TW-L4 '.parent::VERSION;

		$config = array_merge($this->default, $config);

		parent::__construct($config);
	}

	/**
	 * Set new config values for the OAuth class like different tokens.
	 *
	 * @param Array $config An array containing the values that should be overwritten.
	 *
	 * @return void
	 */
	public function setNewConfig($config) {
		// The consumer key and secret must always be included when reconfiguring
		$config = array_merge($this->default, $config);
		parent::reconfigure($config);
	}

	/**
	 * Get a request_token from Twitter
	 *
	 * @param String $oauth_callback [Optional] The callback provided for Twitter's API.
	 * 				The user will be redirected there after authorizing your app on Twitter.
	 *
	 * @returns Array|Bool a key/value array containing oauth_token and oauth_token_secret
	 * 						in case of success
	 */
	function getRequestToken($oauth_callback = NULL) {
		$parameters = array();
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}
		parent::request(self::METHOD_GET, parent::url(Config::get('thujohn/twitter::REQUEST_TOKEN_URL'), ''),  $parameters);

		$response = $this->response;
		if(isset($response['code']) && $response['code'] == 200 && !empty($response)) {
			$get_parameters = $response['response'];
			$token = array();
			parse_str($get_parameters, $token);
		}

		// Return the token if it was properly retrieved
		if( isset($token['oauth_token'], $token['oauth_token_secret']) ){
			return $token;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get an access token for a logged in user
	 *
	 * @returns Array|Bool key/value array containing the token in case of success
	 */
	function getAccessToken($oauth_verifier = FALSE) {
		$parameters = array();
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}

		parent::request(self::METHOD_GET, parent::url(Config::get('thujohn/twitter::ACCESS_TOKEN_URL'), ''),  $parameters);

		$response = $this->response;
		if(isset($response['code']) && $response['code'] == 200 && !empty($response)) {
			$get_parameters = $response['response'];
			$token = array();
			parse_str($get_parameters, $token);
			// Reconfigure the tmhOAuth class with the new tokens
			$this->setNewConfig(array('token' => $token['oauth_token'], 'secret' => $token['oauth_token_secret']));
			return $token;
		}
		return FALSE;
	}

	/**
	 * Get the authorize URL
	 *
	 * @returns string
	 */
	function getAuthorizeURL($token, $sign_in_with_twitter = TRUE, $force_login = FALSE) {
		$url					= Config::get('thujohn/twitter::AUTHENTICATE_URL');
		$params['oauth_token']	= !empty($token['oauth_token']) ? $token['oauth_token'] : NULL;
		if ($force_login) {
			$params['force_login'] = TRUE;
		} else if (empty($sign_in_with_twitter)) {
			$url = Config::get('thujohn/twitter::AUTHORIZE_URL');
		}

		return $url . '?' . http_build_query($params);
	}

	public function query($name, $requestMethod = self::METHOD_GET, $parameters = array(), $multipart = false)
	{
		parent::user_request(array(
			'method'    => $requestMethod,
			'url'       => parent::url(Config::get('thujohn/twitter::API_VERSION').'/'.$name),
			'params'    => $parameters,
			'multipart' => $multipart
		));

		$response = $this->response;

		$format = 'object';
		if (isset($parameters['format']))
		{
			$format = $parameters['format'];
		}

		switch ($format)
		{
			default :
			case 'object' : $response = json_decode($response['response']);
			break;
			case 'json'   : $response = $response['response'];
			break;
			case 'array'  : $response = json_decode($response['response'], true);
			break;
		}

		return $response;
	}

	public function linkify($tweet)
	{
		$tweet = ' '.$tweet;

		$patterns             = array();
		$patterns['url']      = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
		$patterns['mailto']   = '(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))';
		$patterns['user']     = ' +@([a-z0-9_]*)?';
		$patterns['hashtag']  = '(?:(?<=\s)|^)#(\w*[\p{L}-\d\p{Cyrillic}\d]+\w*)';
		$patterns['long_url'] = '>(([[:alnum:]]+:\/\/)|www\.)?([^[:space:]]{12,22})([^[:space:]]*)([^[:space:]]{12,22})([[:alnum:]#?\/&=])<';

		// URL
		$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
		$tweet   = preg_replace_callback('#'.$patterns['url'].'#i', function($matches)
		{
			$input = $matches[0];
			$url   = preg_match('!^https?://!i', $input) ? $input : "http://$input";

			return '<a href="'.$url.'" target="_blank" rel="nofollow">'."$input</a>";
		}, $tweet);

		// Mailto
		$tweet = preg_replace('/'.$patterns['mailto'].'/i', "<a href=\"mailto:\\1\">\\1</a>", $tweet);

		// User
		$tweet = preg_replace('/'.$patterns['user'].'/i', " <a href=\"https://twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);

		// Hashtag
		$tweet = preg_replace('/'.$patterns['hashtag'].'/ui', " <a href=\"https://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $tweet);

		// Long URL
		$tweet = preg_replace('/'.$patterns['long_url'].'/', ">\\3...\\5\\6<", $tweet);

		return trim($tweet);
	}

	public function ago($timestamp)
	{
		if (is_numeric($timestamp) && (int)$timestamp == $timestamp)
		{
			$carbon = Carbon::createFromTimeStamp($timestamp);
		}
		else
		{
			$dt = new \DateTime($timestamp);
			$carbon = Carbon::instance($dt);
		}

		return $carbon->diffForHumans();
	}

	public function linkUser($user)
	{
		return '//twitter.com/' . (is_object($user) ? $user->screen_name : $user);
	}

	public function linkTweet($tweet)
	{
		return $this->linkUser($tweet->user) . '/status/' . $tweet->id_str;
	}

	public function linkRetweet($tweet)
	{
		return '//twitter.com/intent/retweet?tweet_id=' . $tweet->id_str;
	}

	public function linkAddTweetToFavorites($tweet)
	{
		return '//twitter.com/intent/favorite?tweet_id=' . $tweet->id_str;
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
	public function getMentionsTimeline($parameters = array())
	{
		return $this->query('statuses/mentions_timeline', self::METHOD_GET, $parameters);
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
	public function getUserTimeline($parameters = array())
	{
		return $this->query('statuses/user_timeline', self::METHOD_GET, $parameters);
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
	public function getHomeTimeline($parameters = array())
	{
		return $this->query('statuses/home_timeline', self::METHOD_GET, $parameters);
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
	public function getRtsTimeline($parameters = array())
	{
		return $this->query('statuses/retweets_of_me', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 */
	public function getRts($id, $parameters = array())
	{
		return $this->query('statuses/retweets/'.$id, self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 * - include_my_retweet (0|1)
	 * - include_entities (0|1)
	 */
	public function getTweet($id, $parameters = array())
	{
		return $this->query('statuses/show/'.$id, self::METHOD_GET, $parameters);
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
	public function postTweet($parameters = array())
	{
		if (!array_key_exists('status', $parameters))
		{
			throw new \Exception('Parameter required missing : status');
		}

		return $this->query('statuses/update', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function destroyTweet($id, $parameters = array())
	{
		return $this->query('statuses/destroy/'.$id, self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function postRt($id, $parameters = array())
	{
		return $this->query('statuses/retweet/'.$id, self::METHOD_POST, $parameters);
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
	public function postTweetMedia($parameters = array())
	{
		if (!array_key_exists('status', $parameters) || !array_key_exists('media[]', $parameters))
		{
			throw new \Exception('Parameter required missing : status or media[]');
		}

		return $this->query('statuses/update_with_media', self::METHOD_POST, $parameters, true);
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
	public function getOembed($parameters = array())
	{
		if (!array_key_exists('id', $parameters) && !array_key_exists('url', $parameters))
		{
			throw new \Exception('Parameter required missing : id or url');
		}

		return $this->query('statuses/oembed', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getRters($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('statuses/retweeters/ids', self::METHOD_GET, $parameters);
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
	public function getSearch($parameters = array())
	{
		if (!array_key_exists('q', $parameters))
		{
			throw new \Exception('Parameter required missing : q');
		}

		return $this->query('search/tweets', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getDmsIn($parameters = array())
	{
		return $this->query('direct_messages', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - page
	 * - include_entities (0|1)
	 */
	public function getDmsOut($parameters = array())
	{
		return $this->query('direct_messages/sent', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 */
	public function getDm($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('direct_messages/show', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities
	 */
	public function destroyDm($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('direct_messages/destroy', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - text
	 */
	public function postDm($parameters = array())
	{
		if ((!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters)) || !array_key_exists('text', $parameters))
		{
			throw new \Exception('Parameter required missing : user_id, screen_name or text');
		}

		return $this->query('direct_messages/new', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 */
	public function getNoRters($parameters = array())
	{
		return $this->query('friendships/no_retweets/ids', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFriendsIds($parameters = array())
	{
		return $this->query('friends/ids', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFollowersIds($parameters = array())
	{
		return $this->query('followers/ids', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function getFriendshipsLookup($parameters = array())
	{
		return $this->query('friendships/lookup', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsIn($parameters = array())
	{
		return $this->query('friendships/incoming', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsOut($parameters = array())
	{
		return $this->query('friendships/outgoing', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - follow (0|1)
	 */
	public function postFollow($parameters = array())
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		return $this->query('friendships/create', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postUnfollow($parameters = array())
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		return $this->query('friendships/destroy', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - device (0|1)
	 * - retweets (0|1)
	 */
	public function postFollowUpdate($parameters = array())
	{
		if (!array_key_exists('screen_name', $parameters) && !array_key_exists('user_id', $parameters))
		{
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		return $this->query('friendships/update', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - source_id
	 * - source_screen_name
	 * - target_id
	 * - target_screen_name
	 */
	public function getFriendships($parameters = array())
	{
		return $this->query('friendships/show', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFriends($parameters = array())
	{
		return $this->query('friends/list', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFollowers($parameters = array())
	{
		return $this->query('followers/list', self::METHOD_GET, $parameters);
	}

	public function getSettings($parameters)
	{
		return $this->query('account/settings', self::METHOD_GET, $parameters);
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
	public function postSettings($parameters = array())
	{
		if (empty($parameters))
		{
			throw new \Exception('Parameter missing');
		}

		return $this->query('account/settings', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - device (sms|none)
	 * - include_entities (0|1)
	 */
	public function postSettingsDevice($parameters = array())
	{
		if (!array_key_exists('device', $parameters))
		{
			throw new \Exception('Parameter required missing : device');
		}

		return $this->query('account/update_delivery_device', self::METHOD_POST, $parameters);
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
	public function postProfile($parameters = array())
	{
		if (empty($parameters))
		{
			throw new \Exception('Parameter missing');
		}

		return $this->query('account/update_profile', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - image
	 * - tile
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - use (0|1)
	 */
	public function postBackground($parameters = array())
	{
		if (!array_key_exists('image', $parameters) || !array_key_exists('tile', $parameters) || !array_key_exists('use', $parameters))
		{
			throw new \Exception('Parameter required missing : image, tile or use');
		}

		return $this->query('account/update_profile_background_image', self::METHOD_POST, $parameters, true);
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
	public function postColors($parameters = array())
	{
		if (empty($parameters))
		{
			throw new \Exception('Parameter missing');
		}

		return $this->query('account/update_profile_colors', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - image
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postProfileImage($parameters = array())
	{
		if (!array_key_exists('image', $parameters))
		{
			throw new \Exception('Parameter required missing : image');
		}

		return $this->query('account/update_profile_image', self::METHOD_POST, $parameters, true);
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getCredentials($parameters = array())
	{
		return $this->query('account/verify_credentials', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - cursor
	 */
	public function getBlocks($parameters = array())
	{
		return $this->query('blocks/list', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 * - cursor
	 */
	public function getBlocksIds($parameters = array())
	{
		return $this->query('blocks/ids', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postBlock($parameters = array())
	{
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters))
		{
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		return $this->query('blocks/create', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function destroyBlock($parameters = array())
	{
		if (!array_key_exists('screen_name', $parameters) || !array_key_exists('user_id', $parameters))
		{
			throw new \Exception('Parameter required missing : screen_name or user_id');
		}

		return $this->query('blocks/destroy', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsers($parameters = array())
	{
		if (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		{
			throw new \Exception('Parameter required missing : user_id or screen_name');
		}

		return $this->query('users/show', self::METHOD_GET, $parameters);
	}

	/**
	 * Prameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsersLookup($parameters = array())
	{
		if (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		{
			throw new \Exception("Parameter required missing : user_id or screen_name");
		}

		return $this->query('users/lookup', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - q
	 * - page
	 * - count
	 * - include_entities (0|1)
	 */
	public function getUsersSearch($parameters = array())
	{
		if (!array_key_exists('q', $parameters))
		{
			throw new \Exception('Parameter required missing : q');
		}

		return $this->query('users/search', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributees($parameters = array())
	{
		return $this->query('users/contributees', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributors($parameters = array())
	{
		return $this->query('users/contributors', self::METHOD_GET, $parameters);
	}

	/**
	 * Removes the uploaded profile banner for the authenticating user
	 */
	public function destroyUserBanner($parameters = array())
	{
		return $this->query('account/remove_profile_banner', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - banner
	 * - width
	 * - height
	 * - offset_left
	 * - offset_top
	 */
	public function postUserBanner($parameters = array())
	{
		if (!array_key_exists('banner', $parameters)){
			throw new \Exception('Parameter required missing : banner');
		}

		return $this->query('account/update_profile_banner', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 */
	public function getUserBanner($parameters = array())
	{
		return $this->query('users/profile_banner', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggesteds($slug, $parameters = array())
	{
		return $this->query('users/suggestions/'.$slug, self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggestions($parameters = array())
	{
		return $this->query('users/suggestions', self::METHOD_GET, $parameters);
	}

	public function getSuggestedsMembers($slug)
	{
		return $this->query('users/suggestions/'.$slug.'/members', self::METHOD_GET);
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
	public function getFavorites($parameters = array())
	{
		return $this->query('favorites/list', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function destroyFavorite($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('favorites/destroy', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function postFavorite($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('favorites/create', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - reverse (0|1)
	 */
	public function getLists($parameters = array())
	{
		return $this->query('lists/list', self::METHOD_GET, $parameters);
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
	public function getListsStatuses($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/statuses', self::METHOD_GET, $parameters);
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
	public function destroyListMember($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('owner_screen_name', $parameters) || !array_key_exists('owner_id', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id, slug, owner_screen_name or owner_id');
		}

		return $this->query('lists/members/destroy', self::METHOD_POST, $parameters);
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
	public function getListsSubscribers($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/subscribers', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function postListSubscriber($parameters = array())
	{
		if (!array_key_exists('owner_screen_name', $parameters) || !array_key_exists('owner_id', $parameters) || !array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : owner_screen_name, owner_id, list_id or slug');
		}

		return $this->query('lists/subscribers/create', self::METHOD_POST, $parameters);
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
	public function getListSubscriber($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters) || !array_key_exists('user_id', $parameters) || !array_key_exists('screen_name', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
		}

		return $this->query('lists/subscribers/show', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListSubscriber($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) || !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/subscribers/destroy', self::METHOD_POST, $parameters);
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
	public function postListCreateAll($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/members/create_all', self::METHOD_POST, $parameters);
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
	public function getListMember($parameters = array())
	{
        if(!(
            (array_key_exists('list_id', $parameters) && array_key_exists('user_id', $parameters)) ||
            (array_key_exists('list_id', $parameters) && array_key_exists('screen_name', $parameters)) ||
            (array_key_exists('slug', $parameters) && array_key_exists('owner_screen_name', $parameters) && array_key_exists('user_id', $parameters)) ||
            (array_key_exists('slug', $parameters) && array_key_exists('owner_screen_name', $parameters) && array_key_exists('screen_name', $parameters)) ||
            (array_key_exists('slug', $parameters) && array_key_exists('owner_id', $parameters) && array_key_exists('user_id', $parameters)) ||
            (array_key_exists('slug', $parameters) && array_key_exists('owner_id', $parameters) && array_key_exists('screen_name', $parameters))
        )){
            throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
        }

		return $this->query('lists/members/show', self::METHOD_GET, $parameters);
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
	public function getListMembers($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && (!array_key_exists('slug', $parameters) ||
                (array_key_exists('slug', $parameters) && !array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters))
            )
        ){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/members', self::METHOD_GET, $parameters);
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
	public function postListMember($parameters = array())
	{
		if (
			(!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
			|| 
			(!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters))
		) {
			throw new \Exception('Parameter required missing : list_id, slug, user_id or screen_name');
		}

		return $this->query('lists/members/create', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function destroyList($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/destroy', self::METHOD_POST, $parameters);
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
	public function postListUpdate($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/update', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 */
	public function postList($parameters = array())
	{
		if (!array_key_exists('name', $parameters))
		{
			throw new \Exception('Parameter required missing : name');
		}

		return $this->query('lists/create', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function getList($parameters = array())
	{
        if (!array_key_exists('list_id', $parameters) && (!array_key_exists('slug', $parameters) ||
                (array_key_exists('slug', $parameters) && !array_key_exists('owner_screen_name', $parameters) && !array_key_exists('owner_id', $parameters))
            )
        ){
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/show', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListSubscriptions($parameters = array())
	{
		return $this->query('lists/subscriptions', self::METHOD_GET, $parameters);
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
	public function destroyListMembers($parameters = array())
	{
		if (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters))
		{
			throw new \Exception('Parameter required missing : list_id or slug');
		}

		return $this->query('lists/members/destroy_all', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListOwnerships($parameters = array())
	{
		return $this->query('lists/ownerships', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns the authenticated user’s saved search queries
	 */
	public function getSavedSearches($parameters = array())
	{
		return $this->query('saved_searches/list', self::METHOD_GET, $parameters);
	}

	/**
	 * Retrieve the information for the saved search represented by the given id.
	 * The authenticating user must be the owner of saved search ID being requested.
	 */
	public function getSavedSearch($id)
	{
		return $this->query('saved_searches/show/'.$id, self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - query
	 */
	public function postSavedSearch($parameters = array())
	{
		if (!array_key_exists('query', $parameters))
		{
			throw new \Exception('Parameter required missing : query');
		}

		return $this->query('saved_searches/create', self::METHOD_POST, $parameters);
	}

	public function destroySavedSearch($id, $parameters = array())
	{
		return $this->query('saved_searches/destroy/'.$id, self::METHOD_POST, $parameters);
	}

	public function getGeo($id)
	{
		return $this->query('geo/id/'.$id, self::METHOD_GET);
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
	public function getGeoReverse($parameters = array())
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters))
		{
			throw new \Exception('Parameter required missing : lat or long');
		}

		return $this->query('geo/reverse_geocode', self::METHOD_GET, $parameters);
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
	public function getGeoSearch($parameters = array())
	{
		return $this->query('geo/search', self::METHOD_GET, $parameters);
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
	public function getGeoSimilar($parameters = array())
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters) || !array_key_exists('name', $parameters))
		{
			throw new \Exception('Parameter required missing : lat, long or name');
		}

		return $this->query('geo/similar_places', self::METHOD_GET, $parameters);
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
	public function postGeo($parameters = array())
	{
		if (!array_key_exists('name', $parameters) || !array_key_exists('contained_within', $parameters) || !array_key_exists('token', $parameters) || !array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters))
		{
			throw new \Exception('Parameter required missing : name, contained_within, token, lat or long');
		}

		return $this->query('geo/place', self::METHOD_POST, $parameters);
	}

	/**
	 * Parameters :
	 * - id
	 * - exclude
	 */
	public function getTrendsPlace($parameters = array())
	{
		if (!array_key_exists('id', $parameters))
		{
			throw new \Exception('Parameter required missing : id');
		}

		return $this->query('trends/place', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns the locations that Twitter has trending topic information for.
	 */
	public function getTrendsAvailable($parameters = array())
	{
		return $this->query('trends/available', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 */
	public function getTrendsClosest($parameters = array())
	{
		if (!array_key_exists('lat', $parameters) || !array_key_exists('long', $parameters))
		{
			throw new \Exception('Parameter required missing : lat, long or name');
		}

		return $this->query('trends/closest', self::METHOD_GET, $parameters);
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postSpam($parameters = array())
	{
		if (empty($parameters))
		{
			throw new \Exception('Parameter missing');
		}

		return $this->query('users/report_spam', self::METHOD_POST, $parameters);
	}

	/**
	 * Returns the current configuration used by Twitter including twitter.com
	 * slugs which are not usernames, maximum photo resolutions, and t.co URL lengths.
	 */
	public function getHelpConfiguration($parameters = array())
	{
		return $this->query('help/configuration', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns the list of languages supported by Twitter along with the language code supported by Twitter.
	 */
	public function getHelpLanguages($parameters = array())
	{
		return $this->query('help/languages', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns Twitter’s Privacy Policy.
	 */
	public function getHelpPrivacy($parameters = array())
	{
		return $this->query('help/privacy', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns the Twitter Terms of Service. Note: these are not the same as the Developer Policy.
	 */
	public function getHelpTos($parameters = array())
	{
		return $this->query('help/tos', self::METHOD_GET, $parameters);
	}

	/**
	 * Returns the current rate limits for methods belonging to the specified resource families.
	 */
	public function getAppRateLimit($parameters = array())
	{
		return $this->query('application/rate_limit_status', self::METHOD_GET, $parameters);
	}

}