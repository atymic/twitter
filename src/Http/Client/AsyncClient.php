<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http\Client;

use Atymic\Twitter\Contract\Http\AsyncClient as AsyncClientContract;
use Atymic\Twitter\Exception\AuthException;
use Atymic\Twitter\Exception\ClientException;
use Atymic\Twitter\Http\Client;
use Atymic\Twitter\Http\Factory\BrowserCreator;
use Atymic\Twitter\Http\OAuth2Provider;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use Throwable;

final class AsyncClient extends Client implements AsyncClientContract
{
    private const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';

    protected OAuth2Provider $oAuth2Provider;
    private BrowserCreator $browserCreator;
    protected LoopInterface $loop;
    protected ?AccessTokenInterface $accessToken = null;

    public function __construct(
        BrowserCreator $browserCreator,
        OAuth2Provider $oAuth2Provider,
        bool $debug = false,
        ?LoopInterface $loop = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($debug, $logger);

        $this->browserCreator = $browserCreator;
        $this->oAuth2Provider = $oAuth2Provider;
        $this->loop = $loop ?? Factory::create();
    }

    /**
     * @throws ClientException
     */
    public function request(string $method, string $url, string $body = '', array $parameters = []): PromiseInterface
    {
        try {
            $this->logRequest($method, $url, ['*async' => ['body' => $body, 'params' => $parameters]]);

            $headers = (array) ($parameters[self::KEY_REQUEST_HEADERS] ?? []);
            $headers[self::KEY_HEADER_AUTH] = $this->getAuthHeader();
            $timeLimit = (float) ($parameters[self::KEY_STREAM_STOP_AFTER_SECONDS] ?? 0);
            $finalUrl = sprintf('%s?%s', $url, $this->getQueryParams($parameters));

            if ($timeLimit > 0) {
                $this->loop
                    ->addTimer($timeLimit, fn () => $this->loop->stop());
            }

            return $this->getBrowser()
                ->request($method, $finalUrl, $headers, $body);
        } catch (Throwable $exception) {
            throw $this->deduceClientException($exception);
        }
    }

    /**
     * @throws ClientException
     * @see Browser::requestStreaming()
     */
    public function stream(string $method, string $url, array $parameters = []): PromiseInterface
    {
        try {
            $this->logRequest($method, $url, ['*stream' => $parameters]);

            $contents = $parameters[self::KEY_STREAM_CONTENTS] ?? '';
            $timeLimit = (float) ($parameters[self::KEY_STREAM_STOP_AFTER_SECONDS] ?? 0);
            $finalUrl = sprintf('%s?%s', $url, $this->getQueryParams($parameters));

            if ($timeLimit > 0) {
                $this->loop
                    ->addTimer($timeLimit, fn () => $this->loop->stop());
            }

            return $this->getBrowser()
                ->requestStreaming($method, $finalUrl, [self::KEY_HEADER_AUTH => $this->getAuthHeader()], $contents);
        } catch (Throwable $exception) {
            throw $this->deduceClientException($exception);
        }
    }

    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @throws AuthException
     */
    protected function getAccessToken(): string
    {
        try {
            if ($this->accessToken !== null && !$this->accessToken->hasExpired()) {
                return (string) $this->accessToken;
            }

            $this->accessToken = $this->oAuth2Provider->getAccessToken(self::GRANT_TYPE_CLIENT_CREDENTIALS);

            return (string) $this->accessToken;
        } catch (IdentityProviderException $exception) {
            throw AuthException::fromIdentityProviderException($exception);
        } catch (Exception $exception) {
            throw new AuthException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws AuthException
     */
    private function getAuthHeader(): string
    {
        return sprintf('Bearer %s', $this->getAccessToken());
    }

    private function getBrowser(): Browser
    {
        return $this->browserCreator->create($this->loop);
    }

    private function getQueryParams(array $parameters): string
    {
        $queryParams = $parameters;

        unset(
            $queryParams[self::KEY_STREAM_CONTENTS],
            $queryParams[self::KEY_STREAM_STOP_AFTER_SECONDS],
            $queryParams[self::KEY_REQUEST_HEADERS],
        );

        return http_build_query($queryParams);
    }
}
