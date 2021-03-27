<?php

declare(strict_types=1);

namespace Atymic\Twitter\Service;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Exception\Request\BadRequestException;
use Atymic\Twitter\Exception\Request\ForbiddenRequestException;
use Atymic\Twitter\Exception\Request\NotFoundException;
use Atymic\Twitter\Exception\Request\RateLimitedException;
use Atymic\Twitter\Exception\Request\UnauthorizedRequestException;
use Atymic\Twitter\Exception\RequestException as TwitterRequestException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\InvalidArgumentException as InvalidLogArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class Querier implements QuerierContract
{
    private const URL_FORMAT = 'https://%s/%s/%s%s';

    protected Configuration $config;
    protected HttpClient $httpClient;
    protected ?LoggerInterface $logger;
    protected bool $debug;

    /**
     * Twitter constructor.
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
     * @throws InvalidArgumentException
     */
    private function getHttpClient(Configuration $config): HttpClient
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1(
            [
                'consumer_key' => $config->getConsumerKey(),
                'consumer_secret' => $config->getConsumerSecret(),
                'token' => $config->getAccessToken(),
                'token_secret' => $config->getAccessTokenSecret(),
            ]
        );
        $stack->push($middleware);

        return new HttpClient(
            [
                'handler' => $stack,
                'auth' => 'oauth',
            ]
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function usingCredentials(string $accessToken, string $accessTokenSecret): self
    {
        return new self($this->config->withOauthCredentials($accessToken, $accessTokenSecret), $this->logger);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function usingConfiguration(Configuration $configuration): self
    {
        return new self($configuration, $this->logger);
    }

    /**
     * @throws TwitterRequestException
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

    /**
     * @return mixed
     * @throws GuzzleException
     */
    private function request(string $url, array $parameters, string $method)
    {
        $requestFormat = $parameters[self::KEY_REQUEST_FORMAT] ?? null;
        $responseFormat = $parameters[self::KEY_RESPONSE_FORMAT] ?? $parameters[self::KEY_FORMAT] ?? self::RESPONSE_FORMAT_OBJECT;

        unset($parameters[self::KEY_REQUEST_FORMAT], $parameters[self::KEY_RESPONSE_FORMAT], $parameters[self::KEY_FORMAT]);

        $requestOptions = $this->getRequestOptions($parameters, $method, $requestFormat);
        $response = $this->httpClient->request($method, $url, $requestOptions);

        return $this->formatResponse($response, $responseFormat);
    }

    private function getRequestOptions(array $params, string $requestMethod, ?string $requestFormat): array
    {
        switch ($requestFormat) {
            case self::REQUEST_FORMAT_JSON:
                $paramsKey = RequestOptions::JSON;

                break;
            case self::REQUEST_FORMAT_MULTIPART:
                $paramsKey = RequestOptions::MULTIPART;

                break;
            default:
                $paramsKey = in_array($requestMethod, [self::REQUEST_METHOD_POST, self::REQUEST_METHOD_PUT], true)
                    ? RequestOptions::FORM_PARAMS
                    : RequestOptions::QUERY;

                break;
        }

        return [
            $paramsKey => $params,
        ];
    }

    /**
     * @param Response|ResponseInterface $response
     *
     * @return mixed
     */
    private function formatResponse(Response $response, string $format)
    {
        try {
            $body = (string) $response->getBody();

            switch ($format) {
                case self::RESPONSE_FORMAT_JSON:
                    return $body;
                case self::RESPONSE_FORMAT_ARRAY:
                    return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                case self::RESPONSE_FORMAT_OBJECT:
                default:
                    return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $exception) {
            return null;
        }
    }

    private function transformClientException(GuzzleException $exception): TwitterRequestException
    {
        /** @var null|Response $response */
        $response = method_exists($exception, 'getResponse') ? $exception->getResponse() : null;
        $responseCode = $response !== null ? $response->getStatusCode() : null;

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
                return new TwitterRequestException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws TwitterRequestException
     */
    public function get(string $endpoint, array $parameters = [], bool $multipart = false, ?string $extension = null)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_GET, $parameters, $multipart, $extension);
    }

    /**
     * @throws TwitterRequestException
     */
    public function query(
        string $endpoint,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        ?string $extension = null
    ) {
        try {
            $this->logRequest($endpoint, $requestMethod, $parameters, $multipart);

            $host = !$multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
            $url = $this->buildUrl($host, $this->config->getApiVersion(), $endpoint, $extension);

            if ($multipart) {
                $parameters[self::KEY_REQUEST_FORMAT] = RequestOptions::MULTIPART;
            }

            return $this->request($url, $parameters, $requestMethod);
        } catch (GuzzleException $exception) {
            throw $this->transformClientException($exception);
        }
    }

    private function buildUrl(string $host, string $version, string $name, ?string $extension = null): string
    {
        return sprintf(
            self::URL_FORMAT,
            $host,
            $version,
            $name,
            $extension === null ? '' : sprintf('.%s', $extension)
        );
    }

    /**
     * @throws TwitterRequestException
     */
    public function post(string $endpoint, array $parameters = [], bool $multipart = false)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_POST, $parameters, $multipart);
    }

    /**
     * @throws TwitterRequestException
     */
    public function put(string $endpoint, array $parameters = [])
    {
        return $this->query($endpoint, self::REQUEST_METHOD_PUT, $parameters);
    }
}
