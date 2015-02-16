<?php namespace Thujohn\Twitter;

use Carbon\Carbon as Carbon;
use tmhOAuth;

class Twitter extends tmhOAuth
{

	/**
	 * Store the default config values for the class
	 */
	private $default;

	/**
	 * The Laravel application instance.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	public function __construct( $config = [ ] )
	{

		$this->default = [ ];

		$this->app = app();

		$this->default['consumer_key']    = $this->app['config']->get( 'ttwitter.CONSUMER_KEY' );
		$this->default['consumer_secret'] = $this->app['config']->get( 'ttwitter.CONSUMER_SECRET' );
		$this->default['token']           = $this->app['config']->get( 'ttwitter.ACCESS_TOKEN' );
		$this->default['secret']          = $this->app['config']->get( 'ttwitter.ACCESS_TOKEN_SECRET' );

		if( $this->app['session']->has( 'access_token' ) )
		{
			$access_token = $this->app['session']->get( 'access_token' );

			if( is_array( $access_token ) && isset( $access_token['oauth_token'] ) && isset( $access_token['oauth_token_secret'] ) && !empty( $access_token['oauth_token'] ) && !empty( $access_token['oauth_token_secret'] ) )
			{
				$this->default['token']  = $access_token['oauth_token'];
				$this->default['secret'] = $access_token['oauth_token_secret'];
			}
		}
		$this->default['use_ssl']    = $this->app['config']->get( 'ttwitter.USE_SSL' );
		$this->default['user_agent'] = 'TW-L5 ' . parent::VERSION;

		$config = array_merge( $this->default, $config );

		parent::__construct( $config );
	}

	/**
	 * Set new config values for the OAuth class like different tokens.
	 *
	 * @param Array $config An array containing the values that should be overwritten.
	 *
	 * @return void
	 */
	public function set_new_config( $config )
	{
		// The consumer key and secret must always be included when reconfiguring
		$config = array_merge( $this->default, $config );
		parent::reconfigure( $config );
	}

	/**
	 * Get a request_token from Twitter
	 *
	 * @param String $oauth_callback [Optional] The callback provided for Twitter's API.
	 *                               The user will be redirected there after authorizing your app on Twitter.
	 *
	 * @returns Array|Bool a key/value array containing oauth_token and oauth_token_secret
	 *                        in case of success
	 */
	function getRequestToken( $oauth_callback = null )
	{
		$parameters = [ ];
		if( !empty( $oauth_callback ) )
		{
			$parameters['oauth_callback'] = $oauth_callback;
		}
		parent::request( 'GET', parent::url( $this->app['config']->get( 'ttwitter.REQUEST_TOKEN_URL' ), '' ), $parameters );

		$response = $this->response;
		if( isset( $response['code'] ) && $response['code'] == 200 && !empty( $response ) )
		{
			$get_parameters = $response['response'];
			$token          = [ ];
			parse_str( $get_parameters, $token );
		}

		// Return the token if it was properly retrieved
		if( isset( $token['oauth_token'], $token['oauth_token_secret'] ) )
		{
			return $token;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get an access token for a logged in user
	 *
	 * @returns Array|Bool key/value array containing the token in case of success
	 */
	function getAccessToken( $oauth_verifier = false )
	{
		$parameters = [ ];
		if( !empty( $oauth_verifier ) )
		{
			$parameters['oauth_verifier'] = $oauth_verifier;
		}

		parent::request( 'GET', parent::url( $this->app['config']->get( 'ttwitter.ACCESS_TOKEN_URL' ), '' ), $parameters );

		$response = $this->response;
		if( isset( $response['code'] ) && $response['code'] == 200 && !empty( $response ) )
		{
			$get_parameters = $response['response'];
			$token          = [ ];
			parse_str( $get_parameters, $token );
			// Reconfigure the tmhOAuth class with the new tokens
			$this->set_new_config( [ 'token' => $token['oauth_token'], 'secret' => $token['oauth_token_secret'] ] );

			return $token;
		}

		return false;
	}

	/**
	 * Get the authorize URL
	 *
	 * @returns string
	 */
	function getAuthorizeURL( $token, $sign_in_with_twitter = true, $force_login = false )
	{
		if( is_array( $token ) )
		{
			$token = $token['oauth_token'];
		}
		if( $force_login )
		{
			return $this->app['config']->get( 'ttwitter.AUTHENTICATE_URL' ) . "?oauth_token={$token}&force_login=true";
		}
		else if( empty( $sign_in_with_twitter ) )
		{
			return $this->app['config']->get( 'ttwitter.AUTHORIZE_URL' ) . "?oauth_token={$token}";
		}
		else
		{
			return $this->app['config']->get( 'ttwitter.AUTHENTICATE_URL' ) . "?oauth_token={$token}";
		}
	}

	public function query( $name, $requestMethod = 'GET', $parameters = [ ], $multipart = false )
	{
		parent::user_request( [
			'method'    => $requestMethod,
			'url'       => parent::url( $this->app['config']->get( 'ttwitter.API_VERSION' ) . '/' . $name ),
			'params'    => $parameters,
			'multipart' => $multipart
		] );

		$response = $this->response;

		$format = 'object';
		if( isset( $parameters['format'] ) )
		{
			$format = $parameters['format'];
		}

		switch( $format )
		{
			default :
			case 'object' :
				$response = json_decode( $response['response'] );
				break;
			case 'json'   :
				$response = $response['response'];
				break;
			case 'array'  :
				$response = json_decode( $response['response'], true );
				break;
		}

		return $response;
	}

	public function linkify( $tweet )
	{
		$tweet = ' ' . $tweet;

		$patterns             = [ ];
		$patterns['url']      = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
		$patterns['mailto']   = '(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))';
		$patterns['user']     = ' +@([a-z0-9_]*)?';
		$patterns['hashtag']  = '(?:(?<=\s)|^)#(\w*[\p{L}-\d\p{Cyrillic}\d]+\w*)';
		$patterns['long_url'] = '>(([[:alnum:]]+:\/\/)|www\.)?([^[:space:]]{12,22})([^[:space:]]*)([^[:space:]]{12,22})([[:alnum:]#?\/&=])<';

		// URL
		$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
		$tweet   = preg_replace_callback( '#' . $patterns['url'] . '#i', function ( $matches )
		{
			$input = $matches[0];
			$url   = preg_match( '!^https?://!i', $input ) ? $input : "http://$input";

			return '<a href="' . $url . '" target="_blank" rel="nofollow">' . "$input</a>";
		}, $tweet );

		// Mailto
		$tweet = preg_replace( '/' . $patterns['mailto'] . '/i', "<a href=\"mailto:\\1\">\\1</a>", $tweet );

		// User
		$tweet = preg_replace( '/' . $patterns['user'] . '/i', " <a href=\"https://twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet );

		// Hashtag
		$tweet = preg_replace( '/' . $patterns['hashtag'] . '/ui', " <a href=\"https://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $tweet );

		// Long URL
		$tweet = preg_replace( '/' . $patterns['long_url'] . '/', ">\\3...\\5\\6<", $tweet );

		return trim( $tweet );
	}

	public function ago( $timestamp )
	{
		if( is_numeric( $timestamp ) && (int) $timestamp == $timestamp )
		{
			$carbon = Carbon::createFromTimeStamp( $timestamp );
		}
		else
		{
			$dt     = new \DateTime( $timestamp );
			$carbon = Carbon::instance( $dt );
		}

		return $carbon->diffForHumans();
	}

	public function linkUser( $user )
	{
		return '//twitter.com/' . ( is_object( $user ) ? $user->screen_name : $user );
	}

	public function linkTweet( $tweet )
	{
		return $this->linkUser( $tweet->user ) . '/status/' . $tweet->id_str;
	}

	public function linkRetweet( $tweet )
	{
		return '//twitter.com/intent/retweet?tweet_id=' . $tweet->id_str;
	}

	public function linkAddTweetToFavorites( $tweet )
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
	public function getMentionsTimeline( $parameters = [ ] )
	{
		$response = $this->query( 'statuses/mentions_timeline', 'GET', $parameters );

		return $response;
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
	public function getUserTimeline( $parameters = [ ] )
	{
		$response = $this->query( 'statuses/user_timeline', 'GET', $parameters );

		return $response;
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
	public function getHomeTimeline( $parameters = [ ] )
	{
		$response = $this->query( 'statuses/home_timeline', 'GET', $parameters );

		return $response;
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
	public function getRtsTimeline( $parameters = [ ] )
	{
		$response = $this->query( 'statuses/retweets_of_me', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 */
	public function getRts( $id, $parameters = [ ] )
	{
		$response = $this->query( 'statuses/retweets/' . $id, 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - count (1-200)
	 * - trim_user (0|1)
	 * - include_my_retweet (0|1)
	 * - include_entities (0|1)
	 */
	public function getTweet( $id, $parameters = [ ] )
	{
		$response = $this->query( 'statuses/show/' . $id, 'GET', $parameters );

		return $response;
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
	public function postTweet( $parameters = [ ] )
	{
		if( !array_key_exists( 'status', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : status' );
		}

		$response = $this->query( 'statuses/update', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function destroyTweet( $id, $parameters = [ ] )
	{
		$response = $this->query( 'statuses/destroy/' . $id, 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - trim_user (0|1)
	 */
	public function postRt( $id, $parameters = [ ] )
	{
		$response = $this->query( 'statuses/retweet/' . $id, 'POST', $parameters );

		return $response;
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
	public function postTweetMedia( $parameters = [ ] )
	{
		if( !array_key_exists( 'status', $parameters ) || !array_key_exists( 'media[]', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : status or media[]' );
		}

		$response = $this->query( 'statuses/update_with_media', 'POST', $parameters, true );

		return $response;
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
	public function getOembed( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) && !array_key_exists( 'url', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id or url' );
		}

		$response = $this->query( 'statuses/oembed', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getRters( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'statuses/retweeters/ids', 'GET', $parameters );

		return $response;
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
	public function getSearch( $parameters = [ ] )
	{
		if( !array_key_exists( 'q', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : q' );
		}

		$response = $this->query( 'search/tweets', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getDmsIn( $parameters = [ ] )
	{
		$response = $this->query( 'direct_messages', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - since_id
	 * - max_id
	 * - count (1-200)
	 * - page
	 * - include_entities (0|1)
	 */
	public function getDmsOut( $parameters = [ ] )
	{
		$response = $this->query( 'direct_messages/sent', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 */
	public function getDm( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'direct_messages/show', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities
	 */
	public function destroyDm( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'direct_messages/destroy', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - text
	 */
	public function postDm( $parameters = [ ] )
	{
		if( ( !array_key_exists( 'user_id', $parameters ) && !array_key_exists( 'screen_name', $parameters ) ) || !array_key_exists( 'text', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : user_id, screen_name or text' );
		}

		$response = $this->query( 'direct_messages/new', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 */
	public function getNoRters( $parameters = [ ] )
	{
		$response = $this->query( 'friendships/no_retweets/ids', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFriendsIds( $parameters = [ ] )
	{
		$response = $this->query( 'friends/ids', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - stringify_ids (0|1)
	 * - count (1-5000)
	 */
	public function getFollowersIds( $parameters = [ ] )
	{
		$response = $this->query( 'followers/ids', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function getFriendshipsLookup( $parameters = [ ] )
	{
		$response = $this->query( 'friendships/lookup', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsIn( $parameters = [ ] )
	{
		$response = $this->query( 'friendships/incoming', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - cursor
	 * - stringify_ids (0|1)
	 */
	public function getFriendshipsOut( $parameters = [ ] )
	{
		$response = $this->query( 'friendships/outgoing', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - follow (0|1)
	 */
	public function postFollow( $parameters = [ ] )
	{
		if( !array_key_exists( 'screen_name', $parameters ) && !array_key_exists( 'user_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : screen_name or user_id' );
		}

		$response = $this->query( 'friendships/create', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postUnfollow( $parameters = [ ] )
	{
		if( !array_key_exists( 'screen_name', $parameters ) && !array_key_exists( 'user_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : screen_name or user_id' );
		}

		$response = $this->query( 'friendships/destroy', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - device (0|1)
	 * - retweets (0|1)
	 */
	public function postFollowUpdate( $parameters = [ ] )
	{
		if( !array_key_exists( 'screen_name', $parameters ) && !array_key_exists( 'user_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : screen_name or user_id' );
		}

		$response = $this->query( 'friendships/update', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - source_id
	 * - source_screen_name
	 * - target_id
	 * - target_screen_name
	 */
	public function getFriendships( $parameters = [ ] )
	{
		$response = $this->query( 'friendships/show', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFriends( $parameters = [ ] )
	{
		$response = $this->query( 'friends/list', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 */
	public function getFollowers( $parameters = [ ] )
	{
		$response = $this->query( 'followers/list', 'GET', $parameters );

		return $response;
	}

	public function getSettings( $parameters )
	{
		$response = $this->query( 'account/settings', 'GET', $parameters );

		return $response;
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
	public function postSettings( $parameters = [ ] )
	{
		if( empty( $parameters ) )
		{
			throw new \Exception( 'Parameter missing' );
		}

		$response = $this->query( 'account/settings', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - device (sms|none)
	 * - include_entities (0|1)
	 */
	public function postSettingsDevice( $parameters = [ ] )
	{
		if( !array_key_exists( 'device', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : device' );
		}

		$response = $this->query( 'account/update_delivery_device', 'POST', $parameters );

		return $response;
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
	public function postProfile( $parameters = [ ] )
	{
		if( empty( $parameters ) )
		{
			throw new \Exception( 'Parameter missing' );
		}

		$response = $this->query( 'account/update_profile', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - image
	 * - tile
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - use (0|1)
	 */
	public function postBackground( $parameters = [ ] )
	{
		if( !array_key_exists( 'image', $parameters ) || !array_key_exists( 'tile', $parameters ) || !array_key_exists( 'use', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : image, tile or use' );
		}

		$response = $this->query( 'account/update_profile_background_image', 'POST', $parameters, true );

		return $response;
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
	public function postColors( $parameters = [ ] )
	{
		if( empty( $parameters ) )
		{
			throw new \Exception( 'Parameter missing' );
		}

		$response = $this->query( 'account/update_profile_colors', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - image
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postProfileImage( $parameters = [ ] )
	{
		if( !array_key_exists( 'image', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : image' );
		}

		$response = $this->query( 'account/update_profile_image', 'POST', $parameters, true );

		return $response;
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getCredentials( $parameters = [ ] )
	{
		$response = $this->query( 'account/verify_credentials', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 * - cursor
	 */
	public function getBlocks( $parameters = [ ] )
	{
		$response = $this->query( 'blocks/list', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - stringify_ids (0|1)
	 * - cursor
	 */
	public function getBlocksIds( $parameters = [ ] )
	{
		$response = $this->query( 'blocks/ids', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function postBlock( $parameters = [ ] )
	{
		if( !array_key_exists( 'screen_name', $parameters ) || !array_key_exists( 'user_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : screen_name or user_id' );
		}

		$response = $this->query( 'blocks/create', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function destroyBlock( $parameters = [ ] )
	{
		if( !array_key_exists( 'screen_name', $parameters ) || !array_key_exists( 'user_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : screen_name or user_id' );
		}

		$response = $this->query( 'blocks/destroy', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsers( $parameters = [ ] )
	{
		if( !array_key_exists( 'user_id', $parameters ) && !array_key_exists( 'screen_name', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : user_id or screen_name' );
		}

		$response = $this->query( 'users/show', 'GET', $parameters );

		return $response;
	}

	/**
	 * Prameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 */
	public function getUsersLookup( $parameters = [ ] )
	{
		if( !array_key_exists( 'user_id', $parameters ) && !array_key_exists( 'screen_name', $parameters ) )
		{
			throw new \Exception( "Parameter required missing : user_id or screen_name" );
		}

		$response = $this->query( 'users/lookup', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - q
	 * - page
	 * - count
	 * - include_entities (0|1)
	 */
	public function getUsersSearch( $parameters = [ ] )
	{
		if( !array_key_exists( 'q', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : q' );
		}

		$response = $this->query( 'users/search', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributees( $parameters = [ ] )
	{
		$response = $this->query( 'users/contributees', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - include_entities (0|1)
	 * - skip_status (0|1)
	 */
	public function getUsersContributors( $parameters = [ ] )
	{
		$response = $this->query( 'users/contributors', 'GET', $parameters );

		return $response;
	}

	public function destroyUserBanner( $parameters )
	{
		$response = $this->query( 'account/remove_profile_banner', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - banner
	 * - width
	 * - height
	 * - offset_left
	 * - offset_top
	 */
	public function postUserBanner( $parameters = [ ] )
	{
		if( !array_key_exists( 'banner', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : banner' );
		}

		$response = $this->query( 'account/update_profile_banner', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 */
	public function getUserBanner( $parameters = [ ] )
	{
		$response = $this->query( 'users/profile_banner', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggesteds( $slug, $parameters = [ ] )
	{
		$response = $this->query( 'users/suggestions/' . $slug, 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - lang
	 */
	public function getSuggestions( $parameters = [ ] )
	{
		$response = $this->query( 'users/suggestions', 'GET', $parameters );

		return $response;
	}

	public function getSuggestedsMembers( $slug )
	{
		$response = $this->query( 'users/suggestions/' . $slug . '/members', 'GET' );

		return $response;
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
	public function getFavorites( $parameters = [ ] )
	{
		$response = $this->query( 'favorites/list', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function destroyFavorite( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'favorites/destroy', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 * - include_entities (0|1)
	 */
	public function postFavorite( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'favorites/create', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - reverse (0|1)
	 */
	public function getLists( $parameters = [ ] )
	{
		$response = $this->query( 'lists/list', 'GET', $parameters );

		return $response;
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
	public function getListsStatuses( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/statuses', 'GET', $parameters );

		return $response;
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
	public function destroyListMember( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) || !array_key_exists( 'slug', $parameters ) || !array_key_exists( 'owner_screen_name', $parameters ) || !array_key_exists( 'owner_id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id, slug, owner_screen_name or owner_id' );
		}

		$response = $this->query( 'lists/members/destroy', 'POST', $parameters );

		return $response;
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
	public function getListsSubscribers( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) || !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/subscribers', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function postListSubscriber( $parameters = [ ] )
	{
		if( !array_key_exists( 'owner_screen_name', $parameters ) || !array_key_exists( 'owner_id', $parameters ) || !array_key_exists( 'list_id', $parameters ) || !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : owner_screen_name, owner_id, list_id or slug' );
		}

		$response = $this->query( 'lists/subscribers/create', 'POST', $parameters );

		return $response;
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
	public function getListSubscriber( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) || !array_key_exists( 'slug', $parameters ) || !array_key_exists( 'user_id', $parameters ) || !array_key_exists( 'screen_name', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id, slug, user_id or screen_name' );
		}

		$response = $this->query( 'lists/subscribers/show', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function destroyListSubscriber( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) || !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/subscribers/destroy', 'POST', $parameters );

		return $response;
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
	public function postListCreateAll( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/members/create_all', 'POST', $parameters );

		return $response;
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
	public function getListMember( $parameters = [ ] )
	{
		if( !(
			( array_key_exists( 'list_id', $parameters ) && array_key_exists( 'user_id', $parameters ) ) ||
			( array_key_exists( 'list_id', $parameters ) && array_key_exists( 'screen_name', $parameters ) ) ||
			( array_key_exists( 'slug', $parameters ) && array_key_exists( 'owner_screen_name', $parameters ) && array_key_exists( 'user_id', $parameters ) ) ||
			( array_key_exists( 'slug', $parameters ) && array_key_exists( 'owner_screen_name', $parameters ) && array_key_exists( 'screen_name', $parameters ) ) ||
			( array_key_exists( 'slug', $parameters ) && array_key_exists( 'owner_id', $parameters ) && array_key_exists( 'user_id', $parameters ) ) ||
			( array_key_exists( 'slug', $parameters ) && array_key_exists( 'owner_id', $parameters ) && array_key_exists( 'screen_name', $parameters ) )
		)
		)
		{
			throw new \Exception( 'Parameter required missing : list_id, slug, user_id or screen_name' );
		}

		$response = $this->query( 'lists/members/show', 'GET', $parameters );

		return $response;
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
	public function getListMembers( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && ( !array_key_exists( 'slug', $parameters ) ||
				( array_key_exists( 'slug', $parameters ) && !array_key_exists( 'owner_screen_name', $parameters ) && !array_key_exists( 'owner_id', $parameters ) )
			)
		)
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/members', 'GET', $parameters );

		return $response;
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
	public function postListMember( $parameters = [ ] )
	{
		if( (!array_key_exists('list_id', $parameters) && !array_key_exists('slug', $parameters)) || (!array_key_exists('user_id', $parameters) && !array_key_exists('screen_name', $parameters)) )
		{
			throw new \Exception( 'Parameter required missing : list_id, slug, user_id or screen_name' );
		}

		$response = $this->query( 'lists/members/create', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - owner_screen_name
	 * - owner_id
	 * - list_id
	 * - slug
	 */
	public function destroyList( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/destroy', 'POST', $parameters );

		return $response;
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
	public function postListUpdate( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/update', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - name (1-25)
	 * - mode (public|private)
	 * - description
	 */
	public function postList( $parameters = [ ] )
	{
		if( !array_key_exists( 'name', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : name' );
		}

		$response = $this->query( 'lists/create', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - list_id
	 * - slug
	 * - owner_screen_name
	 * - owner_id
	 */
	public function getList( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && ( !array_key_exists( 'slug', $parameters ) ||
				( array_key_exists( 'slug', $parameters ) && !array_key_exists( 'owner_screen_name', $parameters ) && !array_key_exists( 'owner_id', $parameters ) )
			)
		)
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/show', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListSubscriptions( $parameters = [ ] )
	{
		$response = $this->query( 'lists/subscriptions', 'GET', $parameters );

		return $response;
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
	public function destroyListMembers( $parameters = [ ] )
	{
		if( !array_key_exists( 'list_id', $parameters ) && !array_key_exists( 'slug', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : list_id or slug' );
		}

		$response = $this->query( 'lists/members/destroy_all', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - count (1-1000)
	 * - cursor
	 */
	public function getListOwnerships( $parameters = [ ] )
	{
		$response = $this->query( 'lists/ownerships', 'GET', $parameters );

		return $response;
	}

	public function getSavedSearches( $parameters )
	{
		$response = $this->query( 'saved_searches/list', 'GET', $parameters );

		return $response;
	}

	public function getSavedSearch( $id )
	{
		$response = $this->query( 'saved_searches/show/' . $id, 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - query
	 */
	public function postSavedSearch( $parameters = [ ] )
	{
		if( !array_key_exists( 'query', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : query' );
		}

		$response = $this->query( 'saved_searches/create', 'POST', $parameters );

		return $response;
	}

	public function destroySavedSearch( $id, $parameters = [ ] )
	{
		$response = $this->query( 'saved_searches/destroy/' . $id, 'POST', $parameters );

		return $response;
	}

	public function getGeo( $id )
	{
		$response = $this->query( 'geo/id/' . $id, 'GET' );

		return $response;
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
	public function getGeoReverse( $parameters = [ ] )
	{
		if( !array_key_exists( 'lat', $parameters ) || !array_key_exists( 'long', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : lat or long' );
		}

		$response = $this->query( 'geo/reverse_geocode', 'GET', $parameters );

		return $response;
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
	public function getGeoSearch( $parameters = [ ] )
	{
		$response = $this->query( 'geo/search', 'GET', $parameters );

		return $response;
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
	public function getGeoSimilar( $parameters = [ ] )
	{
		if( !array_key_exists( 'lat', $parameters ) || !array_key_exists( 'long', $parameters ) || !array_key_exists( 'name', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : lat, long or name' );
		}

		$response = $this->query( 'geo/similar_places', 'GET', $parameters );

		return $response;
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
	public function postGeo( $parameters = [ ] )
	{
		if( !array_key_exists( 'name', $parameters ) || !array_key_exists( 'contained_within', $parameters ) || !array_key_exists( 'token', $parameters ) || !array_key_exists( 'lat', $parameters ) || !array_key_exists( 'long', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : name, contained_within, token, lat or long' );
		}

		$response = $this->query( 'geo/place', 'POST', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - id
	 * - exclude
	 */
	public function getTrendsPlace( $parameters = [ ] )
	{
		if( !array_key_exists( 'id', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : id' );
		}

		$response = $this->query( 'trends/place', 'GET', $parameters );

		return $response;
	}

	public function getTrendsAvailable( $parameters )
	{
		$response = $this->query( 'trends/available', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - lat
	 * - long
	 */
	public function getTrendsClosest( $parameters = [ ] )
	{
		if( !array_key_exists( 'lat', $parameters ) || !array_key_exists( 'long', $parameters ) )
		{
			throw new \Exception( 'Parameter required missing : lat, long or name' );
		}

		$response = $this->query( 'trends/closest', 'GET', $parameters );

		return $response;
	}

	/**
	 * Parameters :
	 * - screen_name
	 * - user_id
	 */
	public function postSpam( $parameters = [ ] )
	{
		if( empty( $parameters ) )
		{
			throw new \Exception( 'Parameter missing' );
		}

		$response = $this->query( 'users/report_spam', 'POST', $parameters );

		return $response;
	}

	public function getHelpConfiguration( $parameters )
	{
		$response = $this->query( 'help/configuration', 'GET', $parameters );

		return $response;
	}

	public function getHelpLanguages( $parameters )
	{
		$response = $this->query( 'help/languages', 'GET', $parameters );

		return $response;
	}

	public function getHelpPrivacy( $parameters )
	{
		$response = $this->query( 'help/privacy', 'GET', $parameters );

		return $response;
	}

	public function getHelpTos( $parameters )
	{
		$response = $this->query( 'help/tos', 'GET', $parameters );

		return $response;
	}

	public function getAppRateLimit( $parameters )
	{
		$response = $this->query( 'application/rate_limit_status', 'GET', $parameters );

		return $response;
	}

}
