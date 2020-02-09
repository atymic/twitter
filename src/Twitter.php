<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Atymic\Twitter\Exception\Request\BadRequestException;
use Atymic\Twitter\Exception\Request\ForbiddenRequestException;
use Atymic\Twitter\Exception\Request\NotFoundException;
use Atymic\Twitter\Exception\Request\RateLimitedException;
use Atymic\Twitter\Exception\Request\UnauthorizedRequestException;
use Atymic\Twitter\Exception\RequestException as TwitterRequestException;
use Atymic\Twitter\Traits\AccountTrait;
use Atymic\Twitter\Traits\AuthTrait;
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
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use Psr\Log\InvalidArgumentException as InvalidLogArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Twitter
{
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
        UserTrait,
        AuthTrait;

    public const VERSION = '3.x-dev';

    public const KEY_FORMAT = 'format';
    public const KEY_OAUTH_CALLBACK = 'oauth_callback';
    public const KEY_OAUTH_VERIFIER = 'oauth_verifier';
    public const KEY_OAUTH_TOKEN = 'oauth_token';
    public const KEY_OAUTH_TOKEN_SECRET = 'oauth_token_secret';

    public const RESPONSE_FORMAT_ARRAY = 'array';
    public const RESPONSE_FORMAT_OBJECT = 'object';
    public const RESPONSE_FORMAT_JSON = 'json';

    private const DEFAULT_EXTENSION = 'json';
    private const REQUEST_METHOD_GET = 'GET';
    private const REQUEST_METHOD_POST = 'POST';
    private const URL_FORMAT = 'https://%s/%s/%s.%s';

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Twitter constructor.
     *
     * @param Configuration        $config
     * @param null|LoggerInterface $logger
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Configuration $config, ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->debug = $config->isDebugMode();
        $this->httpClient = $this->getHttpClient($config);
    }

    /**
     * @param string $accessToken
     * @param string $accessTokenSecret
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function usingCredentials(string $accessToken, string $accessTokenSecret): self
    {
        return new self($this->config->withOauthCredentials($accessToken, $accessTokenSecret), $this->logger);
    }

    /**
     * @param Configuration $configuration
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function usingConfiguration(Configuration $configuration): self
    {
        return new self($configuration, $this->logger);
    }

    /**
     * @param string $endpoint
     * @param string $requestMethod
     * @param array  $parameters
     * @param bool   $multipart
     * @param string $extension
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function query(
        string $endpoint,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        string $extension = self::DEFAULT_EXTENSION
    ) {
        try {
            $this->logRequest($endpoint, $requestMethod, $parameters, $multipart);

            $host = !$multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
            $url = $this->buildUrl($host, $this->config->getApiVersion(), $endpoint, $extension);

            return $this->request($url, $parameters, $requestMethod);
        } catch (GuzzleException $exception) {
            throw $this->transformClientException($exception);
        }
    }

    /**
     * @param string $url
     * @param string $requestMethod
     * @param array  $parameters
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function directQuery(
        string $url,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = []
    ) {
        try {
            $this->logRequest($url, $requestMethod, $parameters);

            return $this->request($url, $parameters, $requestMethod);
        } catch (GuzzleException $exception) {
            throw $this->transformClientException($exception);
        }
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     * @param bool   $multipart
     * @param string $extension
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function get(string $endpoint, $parameters = [], $multipart = false, $extension = self::DEFAULT_EXTENSION)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_GET, $parameters, $multipart, $extension);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     * @param bool   $multipart
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function post(string $endpoint, $parameters = [], $multipart = false)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_POST, $parameters, $multipart);
    }

    /**
     * @param Configuration $config
     *
     * @throws InvalidArgumentException
     *
     * @return HttpClient
     */
    private function getHttpClient(Configuration $config): HttpClient
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $config->getConsumerKey(),
            'consumer_secret' => $config->getConsumerSecret(),
            'token' => $config->getAccessToken(),
            'token_secret' => $config->getAccessTokenSecret(),
        ]);
        $stack->push($middleware);

        return new HttpClient([
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
    }

    /**
     * @param array  $params
     * @param string $requestMethod
     *
     * @return array
     */
    private function getRequestOptions(array $params, string $requestMethod): array
    {
        unset($params[self::KEY_FORMAT]);

        $paramsKey = $requestMethod === self::REQUEST_METHOD_POST ? RequestOptions::FORM_PARAMS : RequestOptions::QUERY;

        return [
            $paramsKey => $params,
        ];
    }

    /**
     * @param string $host
     * @param string $version
     * @param string $name
     * @param string $extension
     *
     * @return string
     */
    private function buildUrl(string $host, string $version, string $name, string $extension): string
    {
        return sprintf(self::URL_FORMAT, $host, $version, $name, $extension);
    }

    /**
     * @param GuzzleException $exception
     *
     * @return TwitterRequestException
     */
    private function transformClientException(GuzzleException $exception): TwitterRequestException
    {
        /** @var null|Response $response */
        $response = method_exists($exception, 'getResponse') ? $exception->getResponse() : null;
        $responseCode = !empty($response) ? $response->getStatusCode() : null;

        switch ($responseCode) {
            case 400:
                return BadRequestException::fromClientResponse($response, $exception);
            case 401:
                return UnauthorizedRequestException::fromClientResponse($response, $exception);
            case 403:
                return ForbiddenRequestException::fromClientResponse($response, $exception);
            case 404:
                return NotFoundException::fromClientResponse($response, $exception);
            case 420:
                return RateLimitedException::fromClientResponse($response, $exception);
            default:
                return TwitterRequestException::fromClientResponse($response, $exception);
        }
    }

    /**
     * @param Response $response
     * @param string   $format
     *
     * @return mixed|string
     */
    private function formatResponse(Response $response, string $format)
    {
        $body = (string)$response->getBody();

        switch ($format) {
            case self::RESPONSE_FORMAT_JSON:
                return $body;
            case self::RESPONSE_FORMAT_ARRAY:
                return json_decode($body, true);
            case self::RESPONSE_FORMAT_OBJECT:
            default:
                return json_decode($body, false);
        }
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param string $method
     *
     * @throws GuzzleException
     * @return mixed|string
     */
    private function request(string $url, array $parameters, string $method)
    {
        $format = $parameters[self::KEY_FORMAT] ?? self::RESPONSE_FORMAT_OBJECT;
        $requestOptions = $this->getRequestOptions($parameters, $method);
        $response = $this->httpClient->request($method, $url, $requestOptions);

        return $this->formatResponse($response, $format);
    }

    /**
     * @param string $name
     * @param string $requestMethod
     * @param array  $parameters
     * @param bool   $multipart
     * @param string $logLevel
     */
    private function logRequest(
        string $name,
        string $requestMethod,
        array $parameters,
        bool $multipart = false,
        string $logLevel = LogLevel::DEBUG
    ): void {
        $message = 'Making Request';
        $context = [
            'method' => $requestMethod,
            'query' => $name,
            'url' => $name,
            'params' => http_build_query($parameters),
            'multipart' => $multipart,
        ];

        if ($this->logger === null) {
            return;
        }

        if (!$this->debug && $logLevel === LogLevel::DEBUG) {
            return;
        }

        try {
            $this->logger->log($logLevel, $message, $context);
        } catch (InvalidLogArgumentException $exception) {
            return;
        }
    }
}
