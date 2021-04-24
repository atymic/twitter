<?php

// You can find the keys here : https://developer.twitter.com/en/portal/projects-and-apps

return [
    'debug' => env('APP_DEBUG', false),

    'api_url' => 'api.twitter.com',
    'upload_url' => 'upload.twitter.com',
    'api_version' => env('TWITTER_API_VERSION', '1.1'),

    'consumer_key' => env('TWITTER_CONSUMER_KEY'),
    'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
    'access_token' => env('TWITTER_ACCESS_TOKEN'),
    'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET'),

    'authenticate_url' => 'https://api.twitter.com/oauth/authenticate',
    'access_token_url' => 'https://api.twitter.com/oauth/access_token',
    'request_token_url' => 'https://api.twitter.com/oauth/request_token',
];
