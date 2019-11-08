<?php

namespace Atymic\Twitter;

use Atymic\Twitter\Traits\AccountTrait;
use Atymic\Twitter\Traits\BlockTrait;
use Atymic\Twitter\Traits\DirectMessageTrait;
use Atymic\Twitter\Traits\FavoriteTrait;
use Atymic\Twitter\Traits\FormattingHelpers;
use Atymic\Twitter\Traits\FriendshipTrait;
use Atymic\Twitter\Traits\GeoTrait;
use Atymic\Twitter\Traits\HelpTrait;
use Atymic\Twitter\Traits\ListTrait;
use Atymic\Twitter\Traits\MediaTrait;
use Atymic\Twitter\Traits\SearchTrait;
use Atymic\Twitter\Traits\StatusTrait;
use Atymic\Twitter\Traits\TrendTrait;
use Atymic\Twitter\Traits\UserTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RunTimeException;

class Twitter
{
    const VERSION = '3.x-dev';

    use FormattingHelpers,
        AccountTrait,
        BlockTrait,
        DirectMessageTrait,
        FavoriteTrait,
        FriendshipTrait,
        GeoTrait,
        HelpTrait,
        ListTrait,
        MediaTrait,
        SearchTrait,
        StatusTrait,
        TrendTrait,
        UserTrait;


    /** @var Configuration */
    protected $config;
    /** @var Client */
    protected $httpClient;
    /** @var LoggerInterface|null */
    protected $logger;

    /** @var bool */
    protected $debug;

    protected $error;

    public function __construct(Configuration $config, ?Client $httpClient = null, ?LoggerInterface $logger = null)
    {
        if ($httpClient === null) {
            $client = new Client();
        }

        $this->debug = $config->isDebugMode();

        // Todo session abstraction

        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function reconfigure($config)
    {
        // TODO implement
    }

    public function log(string $message, array $context = [], string $logLevel = LogLevel::DEBUG): void
    {
        if ($this->logger === null) {
            return;
        }

        if (!$this->debug && $logLevel = LogLevel::DEBUG) {
            return;
        }

        $this->logger->log($logLevel, $message, $context);
    }

    /**
     * Get a request_token from Twitter.
     *
     * @param string $oauth_callback [Optional] The callback provided for Twitter's API. The user will be redirected
     *                               there after authorizing your app on Twitter.
     *
     * @return array|bool a key/value array containing oauth_token and oauth_token_secret in case of success
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
     * @return array|bool key/value array containing the token in case of success
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
                'token' => $token['oauth_token'],
                'secret' => $token['oauth_token_secret'],
            ]);

            return $token;
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
            return $this->tconfig['AUTHENTICATE_URL'] . "?oauth_token={$token}&force_login=true";
        } elseif (empty($sign_in_with_twitter)) {
            return $this->tconfig['AUTHORIZE_URL'] . "?oauth_token={$token}";
        } else {
            return $this->tconfig['AUTHENTICATE_URL'] . "?oauth_token={$token}";
        }
    }

    public function buildUrl(string $host, string $version, string $name, string $extension): string
    {
        return sprintf('https://%s/%s/%s.%s', $host, $version, $name, $extension);
    }

    public function query(
        string $name,
        string $requestMethod = 'GET',
        array $parameters = [],
        bool $multipart = false,
        string $extension = 'json'
    ) {
        $host = $multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
        $url = $this->buildUrl($host, $this->config->getApiVersion(), $name, $extension);
        $format = 'array'; // todo const

        if (isset($parameters['format'])) {
            $format = $parameters['format'];
            unset($parameters['format']);
        }

        $this->log('Making Request', [
            'method' => $requestMethod,
            'query' => $name,
            'url' => $name,
            'params' => http_build_query($parameters),
            'multipart' => $multipart,
            'format' => $format,
        ]);


        $requestOptions = [];

        if ($requestMethod === 'GET') {
            $requestOptions['query'] = $parameters;
        }

        if ($requestMethod === 'POST') {
            $requestOptions['form_params'] = $parameters;
        }

        try {
            $response = $this->httpClient->request($requestMethod, $url, $requestOptions);
        } catch (ClientException $exception) {
            // todo handle this
            throw $exception;
        } catch (ServerException $exception) {
            // todo handle this
            throw $exception;
        } catch (RequestException $exception) {
            // todo handle this
            throw $exception;
        }

        return $this->getResponseAs($response, $format);
    }

    public function getResponseAs(Response $response, string $format)
    {
        $body = (string) $response->getBody();

        // todo const these
        switch ($format) {
            case 'object':
                return $this->jsonDecode($body, false);
            case 'json':
                return $body;
            default:
            case 'array':
                return $this->jsonDecode($body, true);
        }
    }

    public function get($name, $parameters = [], $multipart = false, $extension = 'json')
    {
        return $this->query($name, 'GET', $parameters, $multipart, $extension);
    }

    public function post($name, $parameters = [], $multipart = false)
    {
        return $this->query($name, 'POST', $parameters, $multipart);
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
