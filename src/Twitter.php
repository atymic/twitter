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
        UserTrait;

    public const VERSION = '3.x-dev';
    public const RESPONSE_FORMAT_ARRAY = 'array';
    public const RESPONSE_FORMAT_OBJECT = 'object';
    public const RESPONSE_FORMAT_JSON = 'json';

    private const DEFAULT_EXTENSION = 'json';
    private const REQUEST_METHOD_GET = 'GET';
    private const REQUEST_METHOD_POST = 'POST';
    private const URL_FORMAT = 'https://%s/%s/%s.%s';
    private const KEY_FORM_PARAMS = 'form_params';
    private const KEY_QUERY = 'query';
    private const KEY_FORMAT = 'format';

    private const RESPONSE_CODE_400 = 400;
    private const RESPONSE_CODE_401 = 401;
    private const RESPONSE_CODE_403 = 403;
    private const RESPONSE_CODE_404 = 404;
    private const RESPONSE_CODE_420 = 420;

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
     * @param string $name
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
        string $name,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        string $extension = self::DEFAULT_EXTENSION
    ) {
        try {
            $this->logRequest($name, $requestMethod, $parameters, $multipart);

            $host = !$multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
            $url = $this->buildUrl($host, $this->config->getApiVersion(), $name, $extension);
            $format = $parameters[self::KEY_FORMAT] ?? self::RESPONSE_FORMAT_OBJECT;
            $requestOptions = $this->getRequestOptions($parameters, $requestMethod);
            $response = $this->httpClient->request($requestMethod, $url, $requestOptions);

            return $this->formatResponse($response, $format);
        } catch (GuzzleException $exception) {
            throw $this->transformClientException($exception);
        }
    }

    /**
     * @param        $name
     * @param array  $parameters
     * @param bool   $multipart
     * @param string $extension
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function get($name, $parameters = [], $multipart = false, $extension = self::DEFAULT_EXTENSION)
    {
        return $this->query($name, self::REQUEST_METHOD_GET, $parameters, $multipart, $extension);
    }

    /**
     * @param       $name
     * @param array $parameters
     * @param bool  $multipart
     *
     * @throws TwitterRequestException
     *
     * @return mixed|string
     */
    public function post($name, $parameters = [], $multipart = false)
    {
        return $this->query($name, self::REQUEST_METHOD_POST, $parameters, $multipart);
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

        $paramsKey = $requestMethod === self::REQUEST_METHOD_POST ? self::KEY_FORM_PARAMS : self::KEY_QUERY;

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
            case self::RESPONSE_CODE_400:
                return BadRequestException::fromClientResponse($response, $exception);
            case self::RESPONSE_CODE_401:
                return UnauthorizedRequestException::fromClientResponse($response, $exception);
            case self::RESPONSE_CODE_403:
                return ForbiddenRequestException::fromClientResponse($response, $exception);
            case self::RESPONSE_CODE_404:
                return NotFoundException::fromClientResponse($response, $exception);
            case self::RESPONSE_CODE_420:
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
        bool $multipart,
        string $logLevel = LogLevel::DEBUG
    ): void {
        $message = 'Making Request';
        $context = [
            'method' => $requestMethod,
            self::KEY_QUERY => $name,
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
