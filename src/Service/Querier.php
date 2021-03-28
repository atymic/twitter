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
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\InvalidArgumentException as InvalidLogArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

final class Querier implements QuerierContract
{
    private const URL_FORMAT = 'https://%s/%s/%s%s';

    protected Configuration $config;
    protected ClientInterface $oAuth1HttpClient;
    protected ClientInterface $oAuth2HttpClient;
    protected ClientInterface $activeHttpClient;
    protected ?LoggerInterface $logger;
    protected bool $debug;

    public function __construct(
        Configuration $config,
        ClientInterface $oAuth1HttpClient,
        ClientInterface $oAuth2HttpClient,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->debug = $config->isDebugMode();
        $this->oAuth1HttpClient = $oAuth1HttpClient;
        $this->oAuth2HttpClient = $oAuth2HttpClient;
        $this->activeHttpClient = $oAuth1HttpClient;
        $this->logger = $logger;
    }

    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    public function usingCredentials(string $accessToken, string $accessTokenSecret): self
    {
        return new self(
            $this->config->withOauthCredentials($accessToken, $accessTokenSecret),
            $this->oAuth1HttpClient,
            $this->oAuth2HttpClient,
            $this->logger
        );
    }

    public function usingConfiguration(Configuration $configuration): self
    {
        return new self($configuration, $this->oAuth1HttpClient, $this->oAuth2HttpClient, $this->logger);
    }

    public function withOAuth1Client(): self
    {
        $instance = clone $this;
        $instance->activeHttpClient = $this->oAuth1HttpClient;

        return $instance;
    }

    public function withOAuth2Client(): self
    {
        $instance = clone $this;
        $instance->activeHttpClient = $this->oAuth2HttpClient;

        return $instance;
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
        $stream = $parameters[self::KEY_STREAM] ?? false;

        unset(
            $parameters[self::KEY_REQUEST_FORMAT],
            $parameters[self::KEY_RESPONSE_FORMAT],
            $parameters[self::KEY_FORMAT],
            $parameters[self::KEY_STREAM]
        );

        $requestOptions = $this->getRequestOptions($parameters, $method, $requestFormat, $stream);
        $response = $this->activeHttpClient->request($method, $url, $requestOptions);

        return $this->formatResponse($response, $responseFormat);
    }

    private function getRequestOptions(
        array $params,
        string $requestMethod,
        ?string $requestFormat,
        bool $stream
    ): array {
        $options = [
            RequestOptions::STREAM => $stream,
        ];

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

        $options[$paramsKey] = $params;

        return $options;
    }

    /**
     * @param Response|ResponseInterface $response
     *
     * @return mixed
     */
    private function formatResponse(Response $response, string $format)
    {
        try {
            $body = $response->getBody();
            $content = '';

            while (!$body->eof()) {
                $content .= $body->read(1024);
            }

            switch ($format) {
                case self::RESPONSE_FORMAT_JSON:
                    return $content;
                case self::RESPONSE_FORMAT_ARRAY:
                    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                case self::RESPONSE_FORMAT_OBJECT:
                default:
                    return json_decode($content, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (RuntimeException $exception) {
            $this->logger->error(
                sprintf('A runtime exception occurred when formatting twitter response. %s', $exception->getMessage())
            );

            return null;
        } catch (JsonException $exception) {
            $this->logger->error(
                sprintf('A JSON exception occurred when formatting twitter response. %s', $exception->getMessage())
            );

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

    /**
     * @throws TwitterRequestException
     */
    public function delete(string $endpoint, array $parameters = [])
    {
        return $this->query($endpoint, self::REQUEST_METHOD_DELETE, $parameters);
    }
}
