# Twitter

Twitter API for Laravel 4

You need to create an application and create your access token in the [developer console](https://dev.twitter.com/).

[![Build Status](https://travis-ci.org/thujohn/twitter-l4.png?branch=master)](https://travis-ci.org/thujohn/twitter-l4)


## Installation

Add `thujohn/twitter` to `composer.json`.

    "thujohn/twitter": "dev-master"
    
Run `composer update` to pull down the latest version of Twitter.

Now open up `app/config/app.php` and add the service provider to your `providers` array.

    'providers' => array(
        'Thujohn\Twitter\TwitterServiceProvider',
    )

Now add the alias.

    'aliases' => array(
        'Twitter' => 'Thujohn\Twitter\TwitterFacade',
    )


## Configuration

Run `php artisan config:publish thujohn/twitter` and modify the config file with your own informations.


## Examples

Returns a collection of the most recent Tweets posted by the user indicated by the screen_name or user_id parameters.

	Route::get('/', function()
	{
		return Twitter::getUserTimeline(array('screen_name' => 'thujohn', 'count' => 20));
	});

Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow.

	Route::get('/', function()
	{
		return Twitter::getHomeTimeline(array('count' => 20));
	});

Returns the X most recent mentions (tweets containing a users's @screen_name) for the authenticating user.

	Route::get('/', function()
	{
		return Twitter::getMentionsTimeline(array('count' => 20));
	});

Updates the authenticating user's current status, also known as tweeting.

	Route::get('/', function()
	{
		return Twitter::postTweet(array('status' => 'Laravel is beautiful'));
	});