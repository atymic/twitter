<?php

declare(strict_types=1);

namespace Atymic\Twitter\Service;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Http\AsyncClient;
use Atymic\Twitter\Contract\Http\ClientFactory;
use Atymic\Twitter\Contract\Http\SyncClient;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Exception\ClientException as TwitterClientException;
use Exception;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\Stream\ReadableStreamInterface;
use Throwable;

final class Querier implements QuerierContract
{
    private const URL_FORMAT = 'https://%s/%s/%s%s';

    private Configuration $config;
    private ClientFactory $clientFactory;
    private SyncClient $syncClient;
    private AsyncClient $asyncClient;
    private ?LoggerInterface $logger;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        Configuration $config,
        ClientFactory $clientFactory,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->syncClient = $clientFactory->createSyncClient($config);
        $this->asyncClient = $clientFactory->createAsyncClient($config);
        $this->logger = $logger;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * @codeCoverageIgnore
     * @throws InvalidArgumentException
     */
    public function usingCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self {
        return new self(
            $this->config->withOauthCredentials($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret),
            $this->clientFactory
        );
    }

    /**
     * @codeCoverageIgnore
     * @throws InvalidArgumentException
     */
    public function usingConfiguration(Configuration $configuration): self
    {
        return new self($configuration, $this->clientFactory);
    }

    /**
     * @codeCoverageIgnore
     * @throws InvalidArgumentException
     */
    public function withOAuth1Client(): self
    {
        $instance = clone $this;
        $instance->syncClient = $this->clientFactory->createSyncClient($this->config, false);

        return $instance;
    }

    /**
     * @codeCoverageIgnore
     * @throws InvalidArgumentException
     */
    public function withOAuth2Client(): self
    {
        $instance = clone $this;
        $instance->syncClient = $this->clientFactory->createSyncClient($this->config, true);

        return $instance;
    }

    /**
     * @throws TwitterClientException
     */
    public function directQuery(
        string $url,
        string $method = self::REQUEST_METHOD_GET,
        array $parameters = []
    ) {
        return $this->syncClient->request($method, $url, $parameters);
    }

    /**
     * @throws TwitterClientException
     */
    public function query(
        string $endpoint,
        string $method = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        ?string $extension = null
    ) {
        $host = !$multipart ? $this->config->getApiUrl() : $this->config->getUploadUrl();
        $url = $this->buildUrl($endpoint, $host, $extension);

        if ($multipart) {
            $parameters[self::KEY_REQUEST_FORMAT] = RequestOptions::MULTIPART;
        }

        return $this->syncClient->request($method, $url, $parameters);
    }

    /**
     * @throws TwitterClientException
     */
    public function get(string $endpoint, array $parameters = [], ?string $extension = null)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_GET, $parameters, false, $extension);
    }

    /**
     * @throws TwitterClientException
     */
    public function post(string $endpoint, array $parameters = [], bool $multipart = false)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_POST, $parameters, $multipart);
    }

    /**
     * @throws TwitterClientException
     */
    public function put(string $endpoint, array $parameters = [])
    {
        return $this->query($endpoint, self::REQUEST_METHOD_PUT, $parameters);
    }

    /**
     * @throws TwitterClientException
     */
    public function delete(string $endpoint, array $parameters = [])
    {
        return $this->query($endpoint, self::REQUEST_METHOD_DELETE, $parameters);
    }

    /**
     * @throws TwitterClientException
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/filtered-stream/api-reference API Reference: Filtered Stream
     * @see https://developer.twitter.com/en/docs/twitter-api/tweets/sampled-stream/introduction API Reference: Sampled Stream
     */
    public function getStream(string $endpoint, callable $onData, array $parameters = []): void
    {
        $countLimit = (int) ($parameters[self::KEY_STREAM_STOP_AFTER_COUNT] ?? 0);
        $streamed = 0;

        unset($parameters[self::KEY_STREAM_STOP_AFTER_COUNT]);

        $this->asyncClient->stream(self::REQUEST_METHOD_GET, $this->buildUrl($endpoint), $parameters)->then(
            function (ResponseInterface $response) use ($onData, $countLimit, $streamed) {
                /** @var $stream ReadableStreamInterface */
                $stream = $response->getBody();

                $stream->on(
                    AsyncClient::EVENT_DATA,
                    function (string $chunk) use ($countLimit, $onData, &$streamed) {
                        $streamed++;
                        if ($countLimit > 0 && $streamed >= $countLimit) {
                            $this->asyncClient->loop()
                                ->stop();
                        }

                        return ($onData)($chunk);
                    }
                );
                $stream->on(
                    AsyncClient::EVENT_ERROR,
                    fn (Throwable $error) => $this->forceLog(
                        'Stream [ERROR]: ' . $error->getMessage() . PHP_EOL,
                        'error'
                    )

                );
                $stream->on(
                    AsyncClient::EVENT_CLOSE,
                    fn () => $this->forceLog('Stream [DONE]' . PHP_EOL, 'info')
                );
            }
        )->otherwise(
            function (Exception $exception) {
                $this->forceLog('Exception occurred on stream promise: ' . $exception->getMessage() . PHP_EOL, 'error');
            }
        );

        $this->asyncClient->loop()
            ->run();
    }

    private function forceLog($message, $logMethod): void
    {
        if ($this->logger === null) {
            echo $message;

            return;
        }

        $this->logger->{$logMethod}($message);
    }

    private function buildUrl(string $endpoint, ?string $host = null, ?string $extension = null): string
    {
        return sprintf(
            self::URL_FORMAT,
            $host ?? $this->config->getApiUrl(),
            $this->config->getApiVersion(),
            $endpoint,
            empty($extension) ? '' : sprintf('.%s', $extension)
        );
    }
}
