# Twitter

Twitter API for Laravel 4/5

You need to create an application and create your access token in the [Application Management](https://apps.twitter.com/).

[![Build Status](https://travis-ci.org/thujohn/twitter.png?branch=master)](https://travis-ci.org/thujohn/twitter)


## Installation

Add `thujohn/twitter` to `composer.json`.
```
"thujohn/twitter": "~2.0"
```

Run `composer update` to pull down the latest version of Twitter.

Or run
```
composer require thujohn/twitter
```

Now open up `/config/app.php` and add the service provider to your `providers` array.
```php
'providers' => [
	'Thujohn\Twitter\TwitterServiceProvider',
]
```

Now add the alias.
```php
'aliases' => [
	'Twitter' => 'Thujohn\Twitter\Facades\Twitter',
]
```


## Upgrading from 1.x.x

The package now requires PHP >= 5.4.0

Facade has changed (Thujohn\Twitter\Facades\Twitter)

Config file has been updated (debug, UPLOAD_URL, ACCESS_TOKEN_URL, REQUEST_TOKEN_URL)

set_new_config() has been renamed reconfig()


## Configuration (Laravel 4)

Run `php artisan config:publish thujohn/twitter` and modify the config file with your own informations.
```
/app/config/packages/thujohn/twitter/config.php
```
Also, make sure to remove the env in the config file and replace it with your information.


## Configuration (Laravel 5)

Run `php artisan vendor:publish` and modify the config file with your own information.
```
/config/ttwitter.php
```
With Laravel 5, it's simple to edit the config.php file - in fact you don't even need to touch it! Just add the following to your .env file and you'll be on your way:
```
TWITTER_CONSUMER_KEY = 
TWITTER_CONSUMER_SECRET = 
TWITTER_ACCESS_TOKEN = 
TWITTER_ACCESS_TOKEN_SECRET =
```


## Special parameter

```
format : object|json|array (default:object)
```


## Functions

Linkify : Transforms URLs, @usernames, hashtags into links.
The type of $tweet can be object, array or text.
By sending an object or an array the method will expand links (t.co) too.
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

Updates the authenticating user's current status with media.
```php
Route::get('/', function()
{
	$uploaded_media = Twitter::uploadMedia(['media' => File::get(public_path('filename.jpg'))]);
	return Twitter::postTweet(['status' => 'Laravel is beautiful', 'media_ids' => $uploaded_media->media_id_string]);
});
```


Sign in with twitter
```php
Route::get('twitter/login', ['as' => 'twitter.login', function(){
	// your SIGN IN WITH TWITTER  button should point to this route
	$sign_in_twitter = true;
	$force_login = false;

	// Make sure we make this request w/o tokens, overwrite the default values in case of login.
	Twitter::reconfig(['token' => '', 'secret' => '']);
	$token = Twitter::getRequestToken(route('twitter.callback'));

	if (isset($token['oauth_token_secret']))
	{
		$url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);

		Session::put('oauth_state', 'start');
		Session::put('oauth_request_token', $token['oauth_token']);
		Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

		return Redirect::to($url);
	}

	return Redirect::route('twitter.error');
}]);

Route::get('twitter/callback', ['as' => 'twitter.callback', function() {
	// You should set this route on your Twitter Application settings as the callback
	// https://apps.twitter.com/app/YOUR-APP-ID/settings
	if (Session::has('oauth_request_token'))
	{
		$request_token = [
			'token'  => Session::get('oauth_request_token'),
			'secret' => Session::get('oauth_request_token_secret'),
		];

		Twitter::reconfig($request_token);

		$oauth_verifier = false;

		if (Input::has('oauth_verifier'))
		{
			$oauth_verifier = Input::get('oauth_verifier');
		}

		// getAccessToken() will reset the token for you
		$token = Twitter::getAccessToken($oauth_verifier);

		if (!isset($token['oauth_token_secret']))
		{
			return Redirect::route('twitter.login')->with('flash_error', 'We could not log you in on Twitter.');
		}

		$credentials = Twitter::getCredentials();

		if (is_object($credentials) && !isset($credentials->error))
		{
			// $credentials contains the Twitter user object with all the info about the user.
			// Add here your own user logic, store profiles, create new users on your tables...you name it!
			// Typically you'll want to store at least, user id, name and access tokens
			// if you want to be able to call the API on behalf of your users.

			// This is also the moment to log in your users if you're using Laravel's Auth class
			// Auth::login($user) should do the trick.

			Session::put('access_token', $token);

			return Redirect::to('/')->with('flash_notice', 'Congrats! You\'ve successfully signed in!');
		}

		return Redirect::route('twitter.error')->with('flash_error', 'Crab! Something went wrong while signing you up!');
	}
}]);

Route::get('twitter/error', ['as' => 'twitter.error', function(){
	// Something went wrong, add your own error handling here
}]);

Route::get('twitter/logout', ['as' => 'twitter.logout', function(){
	Session::forget('access_token');
	return Redirect::to('/')->with('flash_notice', 'You\'ve successfully logged out!');
}]);
```


## Debug

First activate debug in the config file.

Then you can access the logs() method.

```php
try
{
	$response = Twitter::getUserTimeline(['count' => 20, 'format' => 'array']);
}
catch (Exception $e)
{
	dd(Twitter::logs());
}

dd($response);
```
