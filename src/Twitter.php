<?php

namespace Atymic\Twitter;

use Atymic\Twitter\Exception\Request\BadRequestException;
use Atymic\Twitter\Exception\Request\ForbiddenRequestException;
use Atymic\Twitter\Exception\Request\NotFoundException;
use Atymic\Twitter\Exception\Request\RateLimitedException;
use Atymic\Twitter\Exception\Request\RequestFailureException;
use Atymic\Twitter\Exception\Request\ServerErrorException;
use Atymic\Twitter\Exception\Request\TwitterRequestException;
use Atymic\Twitter\Exception\Request\UnauthorizedRequestException;
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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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

    public function __construct(Configuration $config, ?LoggerInterface $logger = null, ?Client $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = new Client();
        }

        $this->debug = $config->isDebugMode();

        // Todo session abstraction

        $this->config = $config;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function usingCredentials(string $accessToken, string $accessTokenSecret): self
    {
        return new self(
            $this->config->withOauthCredentials($accessToken, $accessTokenSecret),
            $this->logger,
            $this->httpClient
        );
    }

    public function usingConfiguration(Configuration $configuration): self
    {
        return new self(
            $configuration,
            $this->logger,
            $this->httpClient
        );
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
        $host = !$multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
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
            throw $this->handleClientException($exception);
        } catch (ServerException $exception) {
            throw new ServerErrorException($exception->getResponse());
        } catch (RequestException $exception) {
            throw new RequestFailureException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->getResponseAs($response, $format);
    }

    public function handleClientException(ClientException $exception): TwitterRequestException
    {
        switch ($exception->getResponse()->getStatusCode()) {
            case 400;
                return new BadRequestException($exception->getResponse());
            case 401;
                return new UnauthorizedRequestException($exception->getResponse());
            case 403;
                return new ForbiddenRequestException($exception->getResponse());
            case 404;
                return new NotFoundException($exception->getResponse());
            case 420;
                return new RateLimitedException($exception->getResponse());
            default;
                return new TwitterRequestException($exception->getResponse());
        }
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

    private function jsonDecode($json, $assoc = false)
    {
        // todo is this still needed?
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
        } else {
            return json_decode($json, $assoc);
        }
    }
}
