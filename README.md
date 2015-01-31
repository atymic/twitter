# Twitter

Twitter API for Laravel 5

You need to create an application and create your access token in the [developer console](https://dev.twitter.com/).

[![Build Status](https://travis-ci.org/thujohn/twitter-l4.png?branch=2.0)](https://travis-ci.org/thujohn/twitter-l4)

## Installation

Require this package with composer:

```
composer require thujohn/twitter
```

Now open up `config/app.php` and add the service provider to your `providers` array.
```php
'providers' => [
	'Thujohn\Twitter\TwitterServiceProvider',
]
```

Now add the alias.
```php
'aliases' => [
	'Twitter' => 'Thujohn\Twitter\TwitterFacade',
]
```


## Configuration
Copy the package config to your local config with the publish command:

```
php artisan vendor:publish
```


## Special parameter

```
format : object|json|array (default:object)
```


## Functions

Linkify : Transforms URLs, @usernames, hashtags into links
```php
Twitter::linkify($tweet);
```

Ago : Converts date into difference (2 hours ago)
```php
Twitter::ago($timestamp);
```

LinkUser : Generates a link to a specific user, by their user object (such as $tweet->user), or id/string.
```php
Twitter::linkUser($user);
```

LinkTweet : Generates a link to a specific tweet.
```php
Twitter::linkTweet($tweet);
```


## Examples

Returns a collection of the most recent Tweets posted by the user indicated by the screen_name or user_id parameters.
```php
Route::get('/', function()
{
	return Twitter::getUserTimeline(['screen_name' => 'thujohn', 'count' => 20, 'format' => 'json']);
});
```

Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow.
```php
Route::get('/', function()
{
	return Twitter::getHomeTimeline(['count' => 20, 'format' => 'json']);
});
```

Returns the X most recent mentions (tweets containing a users's @screen_name) for the authenticating user.
```php
Route::get('/', function()
{
	return Twitter::getMentionsTimeline(['count' => 20, 'format' => 'json']);
});
```

Updates the authenticating user's current status, also known as tweeting.
```php
Route::get('/', function()
{
	return Twitter::postTweet(['status' => 'Laravel is beautiful', 'format' => 'json']);
});
```


Sign in with twitter
```php
Route::get('/twitter/login', function()
{
	// your SIGN IN WITH TWITTER  button should point to this route
	$sign_in_twitter = TRUE;
	$force_login = FALSE;
	$callback_url = 'http://' . $_SERVER['HTTP_HOST'] . '/twitter/callback';
	// Make sure we make this request w/o tokens, overwrite the default values in case of login.
	Twitter::set_new_config(['token' => '', 'secret' => '']);
	$token = Twitter::getRequestToken($callback_url);
	if( isset( $token['oauth_token_secret'] ) ) {
		$url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);

		Session::put('oauth_state', 'start');
		Session::put('oauth_request_token', $token['oauth_token']);
		Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

		return Redirect::to($url);
	}
	return Redirect::to('twitter/error');
});

Route::get('/twitter/callback', function() {
	// You should set this route on your Twitter Application settings as the callback
	// https://apps.twitter.com/app/YOUR-APP-ID/settings
	if(Session::has('oauth_request_token')) {
		$request_token = [
			'token' => Session::get('oauth_request_token'),
			'secret' => Session::get('oauth_request_token_secret'),
		];

		Twitter::set_new_config($request_token);

		$oauth_verifier = FALSE;
		if(Input::has('oauth_verifier')) {
			$oauth_verifier = Input::get('oauth_verifier');
		}

		// getAccessToken() will reset the token for you
		$token = Twitter::getAccessToken( $oauth_verifier );
		if( !isset( $token['oauth_token_secret'] ) ) {
			return Redirect::to('/')->with('flash_error', 'We could not log you in on Twitter.');
		}

		$credentials = Twitter::query('account/verify_credentials');
		if( is_object( $credentials ) && !isset( $credentials->error ) ) {
			// $credentials contains the Twitter user object with all the info about the user.
			// Add here your own user logic, store profiles, create new users on your tables...you name it!
			// Typically you'll want to store at least, user id, name and access tokens
			// if you want to be able to call the API on behalf of your users.

			// This is also the moment to log in your users if you're using Laravel's Auth class
			// Auth::login($user) should do the trick.

			return Redirect::to('/')->with('flash_notice', "Congrats! You've successfully signed in!");
		}
		return Redirect::to('/')->with('flash_error', 'Crab! Something went wrong while signing you up!');
	}
});

Route::get('twitter/error', function(){
	// Something went wrong, add your own error handling here
});

```
