<?php

namespace Thujohn\Twitter;

use Carbon\Carbon as Carbon;
use Illuminate\Config\Repository as Config;
use Illuminate\Session\Store as SessionStore;
use RunTimeException;
use Thujohn\Twitter\Traits\AccountActivityTrait;
use Thujohn\Twitter\Traits\AccountTrait;
use Thujohn\Twitter\Traits\BlockTrait;
use Thujohn\Twitter\Traits\DirectMessageTrait;
use Thujohn\Twitter\Traits\FavoriteTrait;
use Thujohn\Twitter\Traits\FriendshipTrait;
use Thujohn\Twitter\Traits\GeoTrait;
use Thujohn\Twitter\Traits\HelpTrait;
use Thujohn\Twitter\Traits\ListTrait;
use Thujohn\Twitter\Traits\MediaTrait;
use Thujohn\Twitter\Traits\SearchTrait;
use Thujohn\Twitter\Traits\StatusTrait;
use Thujohn\Twitter\Traits\TrendTrait;
use Thujohn\Twitter\Traits\UserTrait;
use tmhOAuth;

class Twitter extends tmhOAuth
{
    use AccountActivityTrait;
    use AccountTrait;
    use BlockTrait;
    use DirectMessageTrait;
    use FavoriteTrait;
    use FriendshipTrait;
    use GeoTrait;
    use HelpTrait;
    use ListTrait;
    use MediaTrait;
    use SearchTrait;
    use StatusTrait;
    use TrendTrait;
    use UserTrait;

    /**
     * Store the config values.
     */
    private $tconfig;

    /**
     * Store the config values for the parent class.
     */
    private $parent_config;

    private $session;

    /**
     * Only for debugging.
     */
    private $debug;

    private $log = [];

    private $error;

    public function __construct(Config $config, SessionStore $session)
    {
        if ($config->has('ttwitter::config')) {
            $this->tconfig = $config->get('ttwitter::config');
        } elseif ($config->get('ttwitter')) {
            $this->tconfig = $config->get('ttwitter');
        } else {
            throw new RunTimeException('No config found');
        }

        $this->debug = (isset($this->tconfig['debug']) && $this->tconfig['debug']) ? true : false;
        $this->session = $session;

        $this->parent_config = [];
        $this->parent_config['consumer_key'] = $this->tconfig['CONSUMER_KEY'];
        $this->parent_config['consumer_secret'] = $this->tconfig['CONSUMER_SECRET'];
        $this->parent_config['token'] = $this->tconfig['ACCESS_TOKEN'];
        $this->parent_config['secret'] = $this->tconfig['ACCESS_TOKEN_SECRET'];

        if ($session->has('access_token')) {
            $access_token = $session->get('access_token');

            if (is_array($access_token) && isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret']) && !empty($access_token['oauth_token']) && !empty($access_token['oauth_token_secret'])) {
                $this->parent_config['token'] = $access_token['oauth_token'];
                $this->parent_config['secret'] = $access_token['oauth_token_secret'];
            }
        }

        if ($session->has('bearer')) {
            $this->parent_config['bearer'] = $session->get('bearer');
        }

        $this->parent_config['use_ssl'] = $this->tconfig['USE_SSL'];
        $this->parent_config['user_agent'] = 'LTTW '.parent::VERSION;

        $config = array_merge($this->parent_config, $this->tconfig);

        parent::__construct($this->parent_config);
    }

    /**
     * Set new config values for the OAuth class like different tokens.
     *
     * @param array $config An array containing the values that should be overwritten.
     *
     * @return void
     */
    public function reconfig($config)
    {
        // The consumer key and secret must always be included when reconfiguring
        $config = array_merge($this->parent_config, $config);

        parent::reconfigure($config);

        return $this;
    }

    private function log($message)
    {
        if ($this->debug) {
            $this->log[] = $message;
        }
    }

    public function logs()
    {
        return $this->log;
    }

    /**
     * Get a request_token from Twitter.
     *
     * @param string $oauth_callback [Optional] The callback provided for Twitter's API. The user will be redirected there after authorizing your app on Twitter.
     *
     * @returns Array|Bool a key/value array containing oauth_token and oauth_token_secret in case of success
     */
    public function getRequestToken($oauth_callback = null)
    {
        $parameters = [];

        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }

        parent::request('GET', parent::url($this->tconfig['REQUEST_TOKEN_URL'], ''), $parameters);

        $response = $this->response;

        if (isset($response['code']) && $response['code'] == 200 && !empty($response)) {
            $get_parameters = $response['response'];
            $token = [];
            parse_str($get_parameters, $token);
        }

        // Return the token if it was properly retrieved
        if (isset($token['oauth_token'], $token['oauth_token_secret'])) {
            return $token;
        } else {
            throw new RunTimeException($response['response'], $response['code']);
        }
    }

    /**
     * Get an access token for a logged in user.
     *
     * @returns Array|Bool key/value array containing the token in case of success
     */
    public function getAccessToken($oauth_verifier = null)
    {
        $parameters = [];

        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }

        parent::request('GET', parent::url($this->tconfig['ACCESS_TOKEN_URL'], ''), $parameters);

        $response = $this->response;

        if (isset($response['code']) && $response['code'] == 200 && !empty($response)) {
            $get_parameters = $response['response'];
            $token = [];
            parse_str($get_parameters, $token);

            // Reconfigure the tmhOAuth class with the new tokens
            $this->reconfig([
                'token'  => $token['oauth_token'],
                'secret' => $token['oauth_token_secret'],
            ]);

            return $token;
        }

        throw new RunTimeException($response['response'], $response['code']);
    }

    /**
     * Get application-only bearer token.
     * https://developer.twitter.com/en/docs/basics/authentication/overview/application-only.
     *
     * @return void
     */
    public function getBearerToken()
    {
        $bearer = $this->bearer_token_credentials(); // Encoded consumer_key and consumer_secret for Basic Auth credentials
        $this->request('POST', $this->url('/oauth2/token', null), ['grant_type' => 'client_credentials'], false, false, ['Authorization' => 'Basic '.$bearer]);
        $response = $this->response;

        if (isset($response['code']) && $response['code'] == 200 && !empty($response)) {
            $data = json_decode($response['response']);
            if (isset($data->token_type) && strcasecmp($data->token_type, 'bearer') === 0) {
                $this->reconfig(['bearer'  => $data->access_token]);
                $this->session->put('bearer', $data->access_token); // Save into session for future use

                return $data->access_token;
            }
        }

        throw new RunTimeException($response['response'], $response['code']);
    }

    /**
     * Get the authorize URL.
     *
     * @returns string
     */
    public function getAuthorizeURL($token, $sign_in_with_twitter = true, $force_login = false)
    {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }

        if ($force_login) {
            return $this->tconfig['AUTHENTICATE_URL']."?oauth_token={$token}&force_login=true";
        } elseif (empty($sign_in_with_twitter)) {
            return $this->tconfig['AUTHORIZE_URL']."?oauth_token={$token}";
        } else {
            return $this->tconfig['AUTHENTICATE_URL']."?oauth_token={$token}";
        }
    }

    public function query($name, $requestMethod = 'GET', $parameters = [], $multipart = false, $extension = 'json', $appOnly = false)
    {
        $this->config['host'] = $this->tconfig['API_URL'];

        if ($multipart) {
            $this->config['host'] = $this->tconfig['UPLOAD_URL'];
        }

        $url = parent::url($this->tconfig['API_VERSION'].'/'.$name, $extension);

        $this->log('METHOD : '.$requestMethod);
        $this->log('QUERY : '.$name);
        $this->log('URL : '.$url);
        $this->log('PARAMETERS : '.(is_array($parameters) ? http_build_query($parameters) : $parameters));
        $this->log('MULTIPART : '.($multipart ? 'true' : 'false'));

        if ($appOnly) {
            if (!$this->session->has('bearer')) { // If bearer not in session, get bearer token
                $this->getBearerToken();
            }

            parent::apponly_request(['method' => $requestMethod, 'url' => $url]);
        } else {
            parent::user_request([
                'method'    => $requestMethod,
                'host'      => $name,
                'url'       => $url,
                'params'    => $parameters,
                'multipart' => $multipart,
            ]);
        }

        $response = $this->response;

        $format = 'object';

        if (isset($parameters['format'])) {
            $format = $parameters['format'];
        }

        $this->log('FORMAT : '.$format);

        $error = $response['error'];

        if ($error) {
            $this->log('ERROR_CODE : '.$response['errno']);
            $this->log('ERROR_MSG : '.$response['error']);

            $this->setError($response['errno'], $response['error']);
        }

        if (isset($response['code']) && ($response['code'] < 200 || $response['code'] > 206)) {
            $_response = $this->jsonDecode($response['response'], true);

            if (is_array($_response)) {
                if (array_key_exists('errors', $_response)) {
                    $error_code = $_response['errors'][0]['code'];
                    $error_msg = $_response['errors'][0]['message'];
                } else {
                    $error_code = $response['code'];
                    $error_msg = $response['error'];
                }
            } else {
                $error_code = $response['code'];
                $error_msg = ($error_code == 503) ? 'Service Unavailable' : 'Unknown error';
            }

            $this->log('ERROR_CODE : '.$error_code);
            $this->log('ERROR_MSG : '.$error_msg);

            $this->setError($error_code, $error_msg);

            throw new RunTimeException('['.$error_code.'] '.$error_msg, $response['code']);
        }

        switch ($format) {
            default:
            case 'object': $response = $this->jsonDecode($response['response']);
            break;
            case 'json': $response = $response['response'];
            break;
            case 'array': $response = $this->jsonDecode($response['response'], true);
            break;
        }

        return $response;
    }

    public function get($name, $parameters = [], $multipart = false, $extension = 'json', $appOnly = false)
    {
        return $this->query($name, 'GET', $parameters, $multipart, $extension, $appOnly);
    }

    public function post($name, $parameters = [], $multipart = false, $extension = 'json', $appOnly = false)
    {
        return $this->query($name, 'POST', $parameters, $multipart, $extension, $appOnly);
    }

    public function delete($name, $parameters = [], $multipart = false, $extension = 'json', $appOnly = false)
    {
        return $this->query($name, 'DELETE', $parameters, $multipart, $extension, $appOnly);
    }

    /**
     * Get the last response from twitter.
     */
    public function getLastResponse()
    {
        return $this->response;
    }

    public function linkify($tweet)
    {
        return Utils::linkify($tweet);
    }

    public function ago($timestamp)
    {
        if (is_numeric($timestamp) && (int) $timestamp == $timestamp) {
            $carbon = Carbon::createFromTimeStamp($timestamp);
        } else {
            $dt = new \DateTime($timestamp);
            $carbon = Carbon::instance($dt);
        }

        return $carbon->diffForHumans();
    }

    public function linkUser($user)
    {
        return 'https://twitter.com/'.(is_object($user) ? $user->screen_name : $user);
    }

    public function linkTweet($tweet)
    {
        return $this->linkUser($tweet->user).'/status/'.$tweet->id_str;
    }

    public function linkRetweet($tweet)
    {
        return 'https://twitter.com/intent/retweet?tweet_id='.$tweet->id_str;
    }

    public function linkAddTweetToFavorites($tweet)
    {
        return 'https://twitter.com/intent/favorite?tweet_id='.$tweet->id_str;
    }

    public function linkReply($tweet)
    {
        return 'https://twitter.com/intent/tweet?in_reply_to='.$tweet->id_str;
    }

    public function error()
    {
        return $this->error;
    }

    public function setError($code, $message)
    {
        $this->error = compact('code', 'message');

        return $this;
    }

    private function jsonDecode($json, $assoc = false)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
        } else {
            return json_decode($json, $assoc);
        }
    }
}
