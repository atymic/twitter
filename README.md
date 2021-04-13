# Twitter for PHP

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md) 
[![Build Status](https://img.shields.io/travis/atymic/twitter/master.svg?style=flat-square)](https://travis-ci.org/atymic/twitter) 
[![StyleCI](https://styleci.io/repos/11009743/shield)](https://styleci.io/repos/11009743) 
[![Latest Version on Packagist](https://img.shields.io/packagist/v/atymic/twitter.svg?style=flat-square)](https://packagist.org/packages/thujohn/twitter) 
[![3.x Downloads](https://img.shields.io/packagist/dt/atymic/twitter.svg?style=flat-square&label=3.x%20downloads)](https://packagist.org/packages/atymic/twitter) 
[![2.x Downloads](https://img.shields.io/packagist/dt/thujohn/twitter.svg?style=flat-square&label=2.x%20downloads)](https://packagist.org/packages/thujohn/twitter) 
![GitHub Release Date](https://img.shields.io/github/release-date/atymic/twitter?label=latest%20release&style=flat-square)

Twitter API for Laravel 6.x, 7.x, 8.x (and new versions as they are released).
Also supports other frameworks via PHP-DI (or feel free to add support for your framework via PR)

You need to create an application and create your access token in the [Application Management](https://apps.twitter.com/).

## Installation

```
composer require atymic/twitter
```

## Laravel Configuration 

Just set the below environment variables in your `.env`. 

```
TWITTER_CONSUMER_KEY=
TWITTER_CONSUMER_SECRET=
TWITTER_ACCESS_TOKEN=
TWITTER_ACCESS_TOKEN_SECRET=
TWITTER_API_VERSION=
```

### Advanced Laravel configuration

Run `php artisan vendor:publish --provider="Atymic\Twitter\ServiceProviders\LaravelTwitterServiceProvider"`
```
/config/twitter.php
```

# Versions 

### 3.x 

3.x is the current major version, and is not backward compatible with 2.x.

See the migration guide in [UPGRADE.md](./UPGRADE.md).

### 2.x 

2.x is not longer maintained. We are not accepting bug fixes, please upgrade to 3.x

# Usage

## Output format

You can choose between three different output formats. By default responses will be returned as objects. To change this,
use the `format` option in the parameters you pass to any method. 

```
format : object|json|array (default:object)
```

## Twitter API Versions

To set the default twitter API version to v2 instead of the default `v1.1`, set the  `TWITTER_API_VERSION` to `2` in your `.env`.

If you have set the `v1.1` api as the default, you can use use `Twitter::forApiV2()` to get an instance of the v2 client.
The same goes for getting a `v1` instance from a `v2` client, using `Twitter::forApiV1()`.

It is safe to call `Twitter::forApiV1()` on either a `v1` or `v2` client instance.

## Functions

### Twitter API v1.1

#### Account

* `getSettings()` - Returns settings (including current trend, geo and sleep time information) for the authenticating user.
* `getCredentials()`
* `postSettings()` - Updates the authenticating user’s settings.
* `postSettingsDevice()` - Sets which device Twitter delivers updates to for the authenticating user. Sending none as the device parameter will disable SMS updates.
* `postProfile()` - Sets some values that users are able to set under the “Account” tab of their settings page. Only the parameters specified will be updated. 
* `postBackground()` - Updates the authenticating user’s profile background image. This method can also be used to enable or disable the profile background image.
* `postProfileImage()` - Updates the authenticating user’s profile image. Note that this method expects raw multipart data, not a URL to an image.
* `destroyUserBanner()` - Removes the uploaded profile banner for the authenticating user. Returns HTTP 200 upon success.
* `postUserBanner()` - Uploads a profile banner on behalf of the authenticating user. For best results, upload an profile_banner_url node in their Users objects.

#### Block

* `getBlocks()` - Returns a collection of user objects that the authenticating user is blocking.
* `getBlocksIds()` - Returns an array of numeric user ids the authenticating user is blocking.
* `postBlock()` - Blocks the specified user from following the authenticating user. In addition the blocked user will not show in the authenticating users mentions or timeline (unless retweeted by another user). If a follow or friend relationship exists it is destroyed.
* `destroyBlock()` - Un-blocks the user specified in the ID parameter for the authenticating user. Returns the un-blocked user in the requested format when successful. If relationships existed before the block was instated, they will not be restored.

#### DirectMessage

* `getDm()` - Returns a single direct message event, specified by an id parameter.
* `getDms()` - Returns all Direct Message events (both sent and received) within the last 30 days. Sorted in reverse-chronological order.
* `destroyDm()` - Destroys the direct message specified in the required ID parameter. The authenticating user must be the recipient of the specified direct message.
* `postDm()` - Publishes a new message_create event resulting in a Direct Message sent to a specified user from the authenticating user. Returns an event if successful. Supports publishing Direct Messages with optional Quick Reply and media attachment.

#### Favorite

* `getFavorites()` - Returns the 20 most recent Tweets favorited by the authenticating or specified user.
* `destroyFavorite()` - Un-favorites the status specified in the ID parameter as the authenticating user. Returns the un-favorited status in the requested format when successful.
* `postFavorite()` - Favorites the status specified in the ID parameter as the authenticating user. Returns the favorite status when successful.

#### Friendship

* `getNoRters()` - Returns a collection of user_ids that the currently authenticated user does not want to receive retweets from.
* `getFriendsIds()` - Returns a cursored collection of user IDs for every user following the specified user.
* `getFollowersIds()` - Returns a cursored collection of user IDs for every user following the specified user.
* `getFriendshipsIn()` - Returns a collection of numeric IDs for every user who has a pending request to follow the authenticating user.
* `getFriendshipsOut()` - Returns a collection of numeric IDs for every protected user for whom the authenticating user has a pending follow request.
* `postFollow()` - Allows the authenticating users to follow the user specified in the ID parameter.
* `postUnfollow()` - Allows the authenticating user to unfollow the user specified in the ID parameter.
* `postFollowUpdate()` - Allows one to enable or disable retweets and device notifications from the specified user.
* `getFriendships()` - Returns detailed information about the relationship between two arbitrary users.
* `getFriends()` - Returns a cursored collection of user objects for every user the specified user is following (otherwise known as their “friends”).
* `getFollowers()` - Returns a cursored collection of user objects for users following the specified user.
* `getFriendshipsLookup()` - Returns the relationships of the authenticating user to the comma-separated list of up to 100 screen_names or user_ids provided. Values for connections can be: following, following_requested, followed_by, none, blocking, muting.

#### Geo

* `getGeo()` - Returns all the information about a known place.
* `getGeoReverse()` - Given a latitude and a longitude, searches for up to 20 places that can be used as a place_id when updating a status.
* `getGeoSearch()` - Search for places that can be attached to a statuses/update. Given a latitude and a longitude pair, an IP address, or a name, this request will return a list of all the valid places that can be used as the place_id when updating a status.
* `getGeoSimilar()` - Locates places near the given coordinates which are similar in name. Conceptually you would use this method to get a list of known places to choose from first. Then, if the desired place doesn't exist, make a request to POST geo/place to create a new one. The token contained in the response is the token needed to be able to create a new place.

#### Help

* `postSpam()` - Report the specified user as a spam account to Twitter. Additionally performs the equivalent of POST blocks / create on behalf of the authenticated user.
* `getHelpConfiguration()` - Returns the current configuration used by Twitter including twitter.com slugs which are not usernames, maximum photo resolutions, and t.co URL lengths.
* `getHelpLanguages()` - Returns the list of languages supported by Twitter along with the language code supported by Twitter.
* `getHelpPrivacy()` - Returns Twitter’s Privacy Policy.
* `getHelpTos()` - Returns the Twitter Terms of Service. Note: these are not the same as the Developer Policy.
* `getAppRateLimit()` - Returns the current rate limits for methods belonging to the specified resource families.

#### List

* `getLists()` - Returns all lists the authenticating or specified user subscribes to, including their own. The user is specified using the user_id or screen_name parameters. If no user is given, the authenticating user is used.
* `getListStatuses()` - Returns a timeline of tweets authored by members of the specified list. Retweets are included by default. Use the include_rts=false parameter to omit retweets.
* `destroyListMember()` - Removes the specified member from the list. The authenticated user must be the list’s owner to remove members from the list.
* `getListsMemberships()` - Returns the lists the specified user has been added to. If user_id or screen_name are not provided the memberships for the authenticating user are returned.
* `getListsSubscribers()` - Returns the subscribers of the specified list. Private list subscribers will only be shown if the authenticated user owns the specified list.
* `postListSubscriber()` - Subscribes the authenticated user to the specified list.
* `getListSubscriber()` - Returns the subscribers of the specified list. Private list subscribers will only be shown if the authenticated user owns the specified list.
* `destroyListSubscriber()` - Unsubscribes the authenticated user from the specified list.
* `postListCreateAll()` - Adds multiple members to a list, by specifying a comma-separated list of member ids or screen names. The authenticated user must own the list to be able to add members to it. Note that lists can’t have more than 5,000 members, and you are limited to adding up to 100 members to a list at a time with this method.
* `getListMember()` - Check if the specified user is a member of the specified list.
* `getListMembers()` - Returns the members of the specified list. Private list members will only be shown if the authenticated user owns the specified list.
* `postListMember()` - Add a member to a list. The authenticated user must own the list to be able to add members to it. Note that lists cannot have more than 5,000 members.
* `destroyList()` - Deletes the specified list. The authenticated user must own the list to be able to destroy it.
* `postListUpdate()` - Updates the specified list. The authenticated user must own the list to be able to update it.
* `postList()` - Creates a new list for the authenticated user. Note that you can’t create more than 20 lists per account.
* `getList()` - Returns the specified list. Private lists will only be shown if the authenticated user owns the specified list.
* `getListSubscriptions()` - Obtain a collection of the lists the specified user is subscribed to, 20 lists per page by default. Does not include the user’s own lists.
* `destroyListMembers()` - Removes multiple members from a list, by specifying a comma-separated list of member ids or screen names. The authenticated user must own the list to be able to remove members from it. Note that lists can’t have more than 500 members, and you are limited to removing up to 100 members to a list at a time with this method.
* `getListOwnerships()` - Returns the lists owned by the specified Twitter user. Private lists will only be shown if the authenticated user is also the owner of the lists.

#### Media

* `uploadMedia()` - Upload media (images) to Twitter, to use in a Tweet or Twitter-hosted Card.

#### Search

* `getSearch()` - Returns a collection of relevant Tweets matching a specified query.
* `getSavedSearches()` - Returns the authenticated user’s saved search queries.
* `getSavedSearch()` - Retrieve the information for the saved search represented by the given id. The authenticating user must be the owner of saved search ID being requested.
* `postSavedSearch()` - Create a new saved search for the authenticated user. A user may only have 25 saved searches.
* `destroySavedSearch()` - Destroys a saved search for the authenticating user. The authenticating user must be the owner of saved search id being destroyed.

#### Status

* `getMentionsTimeline()` - Returns the 20 most recent mentions (tweets containing a users’s @screen_name) for the authenticating user.
* `getUserTimeline()` - Returns a collection of the most recent Tweets posted by the user indicated by the screen_name or user_id parameters.
* `getHomeTimeline()` - Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow. The home timeline is central to how most users interact with the Twitter service.
	 *
* `getRtsTimeline()` - Returns the most recent tweets authored by the authenticating user that have been retweeted by others.
* `getRts()` - Returns a collection of the 100 most recent retweets of the tweet specified by the id parameter.
* `getTweet()` - Returns a single Tweet, specified by the id parameter. The Tweet’s author will also be embedded within the tweet.
* `destroyTweet()` - Destroys the status specified by the required ID parameter. The authenticating user must be the author of the specified status. Returns the destroyed status if successful.
* `postTweet()` - Updates the authenticating user’s current status, also known as tweeting.
* `postRt()` -  Retweets a tweet. Returns the original tweet with retweet details embedded.
* `getOembed()` - Returns a single Tweet, specified by either a Tweet web URL or the Tweet ID, in an oEmbed-compatible format. The returned HTML snippet will be automatically recognized as an Embedded Tweet when Twitter’s widget JavaScript is included on the page.
* `getRters()` - Returns a collection of up to 100 user IDs belonging to users who have retweeted the tweet specified by the id parameter.
* `getStatusesLookup()` - Returns fully-hydrated tweet objects for up to 100 tweets per request, as specified by comma-separated values passed to the id parameter.

#### Trend

* `getTrendsPlace()` - Returns the top 10 trending topics for a specific WOEID, if trending information is available for it.
* `getTrendsAvailable()` - Returns the locations that Twitter has trending topic information for.
* `getTrendsClosest()` - Returns the locations that Twitter has trending topic information for, closest to a specified location.

#### User

* `getUsersLookup()` - Returns fully-hydrated user objects for up to 100 users per request, as specified by comma-separated values passed to the user_id and/or screen_name parameters.
* `getUsers()` - Returns a variety of information about the user specified by the required user_id or screen_name parameter. The author’s most recent Tweet will be returned inline when possible.
* `getUsersSearch()` - Provides a simple, relevance-based search interface to public user accounts on Twitter. Try querying by topical interest, full name, company name, location, or other criteria. Exact match searches are not supported.
* `getUserBanner()` - Returns a map of the available size variations of the specified user’s profile banner. If the user has not uploaded a profile banner, a HTTP 404 will be served instead. This method can be used instead of string manipulation on the profile_banner_url returned in user objects as described in Profile Images and Banners.
* `muteUser()` - Mutes the user specified in the ID parameter for the authenticating user.
* `unmuteUser()` - Un-mutes the user specified in the ID parameter for the authenticating user.
* `mutedUserIds()` - Returns an array of numeric user ids the authenticating user has muted.
* `mutedUsers()` - Returns an array of user objects the authenticating user has muted.
* `getSuggesteds()` - Access the users in a given category of the Twitter suggested user list.
* `getSuggestions()` - Access to Twitter’s suggested user list. This returns the list of suggested user categories. The category can be used in GET users / suggestions / :slug to get the users in that category.
* `getSuggestedsMembers()` - Access the users in a given category of the Twitter suggested user list and return their most recent status if they are not a protected user.

### Twitter API v2

#### Tweet Lookup

* `getTweet()` - Returns a variety of information about a single Tweet specified by the requested ID.
* `getTweets()` - Returns a variety of information about the Tweet specified by the requested ID or list of IDs.

#### Search Tweets

* `searchRecent()` - The recent search endpoint returns Tweets from the last seven days that match a search query.
* `searchAll()` - The full-archive search endpoint returns the complete history of public Tweets matching a search query; since the first Tweet was created March 26, 2006.

  **Note:** oThis endpoint is only available to those approved for the Academic Research product track.

#### Timelines

* `userTweets()` - Returns Tweets composed by a single user, specified by the requested user ID. By default, the most recent ten Tweets are returned per request. Using pagination, the most recent 3,200 Tweets can be retrieved.
* `userMentions()` - Returns Tweets mentioning a single user specified by the requested user ID. By default, the most recent ten Tweets are returned per request. Using pagination, up to the most recent 800 Tweets can be retrieved.

#### Filtered Stream

* `getStreamRules()` - Return a list of rules currently active on the streaming endpoint, either as a list or individually.
* `postStreamRules()` - Add or delete rules to your stream.
* `getStream()` - Streams Tweets in real-time based on a specific set of filter rules.

#### Sampled Stream

* `getSampledStream()` - Streams about 1% of all Tweets in real-time.

#### Hide Replies

* `hideTweet()` - Hides or unhides a reply to a Tweet.

## Helper Functions

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
Route::get('/userTimeline', function()
{
	return Twitter::getUserTimeline(['screen_name' => 'thujohn', 'count' => 20, 'response_format' => 'json']);
});
```

Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow.
```php
Route::get('/homeTimeline', function()
{
	return Twitter::getHomeTimeline(['count' => 20, 'response_format' => 'json']);
});
```

Returns the X most recent mentions (tweets containing a users's @screen_name) for the authenticating user.
```php
Route::get('/mentionsTimeline', function()
{
	return Twitter::getMentionsTimeline(['count' => 20, 'response_format' => 'json']);
});
```

Updates the authenticating user's current status, also known as tweeting.
```php
Route::get('/tweet', function()
{
	return Twitter::postTweet(['status' => 'Laravel is beautiful', 'response_format' => 'json']);
});
```

Updates the authenticating user's current status with media.
```php
Route::get('/tweetMedia', function()
{
	$uploaded_media = Twitter::uploadMedia(['media' => File::get(public_path('filename.jpg'))]);
	return Twitter::postTweet(['status' => 'Laravel is beautiful', 'media_ids' => $uploaded_media->media_id_string]);
});
```

Get User Credentials with email.
```php
$credentials = Twitter::getCredentials([
    'include_email' => 'true',
]);
```
> In the above, you need to pass true as a string, not as a boolean. The boolean will get converted to `1` which Twitter ignores.

> This also is assuming you have your permissions setup correctly with Twitter. You have to choose 'Get user email' when you set up your Twitter app, passing the value alone will not be enough.


Sign in with twitter
```php
use Atymic\Twitter\Facades\Twitter;

Route::get('twitter/login', ['as' => 'twitter.login', static function () {
    $token = Twitter::getRequestToken(route('twitter.callback'));

    if (isset($token['oauth_token_secret'])) {
        $url = Twitter::getAuthenticateUrl($token['oauth_token']);

        Session::put('oauth_state', 'start');
        Session::put('oauth_request_token', $token['oauth_token']);
        Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

        return Redirect::to($url);
    }

    return Redirect::route('twitter.error');
}]);

Route::get('twitter/callback', ['as' => 'twitter.callback', static function () {
    // You should set this route on your Twitter Application settings as the callback
    // https://apps.twitter.com/app/YOUR-APP-ID/settings
    if (Session::has('oauth_request_token')) {
        $twitter = Twitter::usingCredentials(session('oauth_request_token'), session('oauth_request_token_secret'));
        $token = $twitter->getAccessToken(request('oauth_verifier'));

        if (!isset($token['oauth_token_secret'])) {
            return Redirect::route('twitter.error')->with('flash_error', 'We could not log you in on Twitter.');
        }

        // use new tokens
        $twitter = Twitter::usingCredentials($token['oauth_token'], $token['oauth_token_secret']);
        $credentials = $twitter->getCredentials();

        if (is_object($credentials) && !isset($credentials->error)) {
            // $credentials contains the Twitter user object with all the info about the user.
            // Add here your own user logic, store profiles, create new users on your tables...you name it!
            // Typically you'll want to store at least, user id, name and access tokens
            // if you want to be able to call the API on behalf of your users.

            // This is also the moment to log in your users if you're using Laravel's Auth class
            // Auth::login($user) should do the trick.

            Session::put('access_token', $token);

            return Redirect::to('/')->with('notice', 'Congrats! You\'ve successfully signed in!');
        }
    }

    return Redirect::route('twitter.error')
            ->with('error', 'Crab! Something went wrong while signing you up!');
}]);

Route::get('twitter/error', ['as' => 'twitter.error', function () {
    // Something went wrong, add your own error handling here
}]);

Route::get('twitter/logout', ['as' => 'twitter.logout', function () {
    Session::forget('access_token');

    return Redirect::to('/')->with('notice', 'You\'ve successfully logged out!');
}]);
```

### Twitter API v2 Examples

Get user tweets:
```php
// ...

use Atymic\Twitter\Twitter as TwitterContract;
use Illuminate\Http\JsonResponse;
use Twitter;

// ... 

public function userTweets(int $userId): JsonResponse
{
	$params = [
		'place.fields' => 'country,name',
		'tweet.fields' => 'author_id,geo',
		'expansions' => 'author_id,in_reply_to_user_id',
		TwitterContract::KEY_RESPONSE_FORMAT => TwitterContract::RESPONSE_FORMAT_JSON,
	];

	return JsonResponse::fromJsonString(Twitter::userTweets($userId, $params));
}
```

Search tweets:
```php
// ...
public function searchRecent(string $query): JsonResponse
{
    $params = [
        'place.fields' => 'country,name',
        'tweet.fields' => 'author_id,geo',
        'expansions' => 'author_id,in_reply_to_user_id',
        TwitterContract::KEY_RESPONSE_FORMAT => TwitterContract::RESPONSE_FORMAT_JSON,
    ];

    return JsonResponse::fromJsonString(Twitter::searchRecent($query, $params));
}
// ...
```

## Debug

First activate debug mode in the config file.

Then you can access the logs() method.

```php
try
{
	$response = Twitter::getUserTimeline(['count' => 20, 'response_format' => 'array']);
}
catch (Exception $e)
{
	// dd(Twitter::error());
	dd(Twitter::logs());
}

dd($response);
```
