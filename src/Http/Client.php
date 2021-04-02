<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http;

use Atymic\Twitter\Contract\Http\Client as ClientContract;
use Atymic\Twitter\Exception\ClientException as TwitterClientException;
use Atymic\Twitter\Exception\Request\BadRequestException;
use Atymic\Twitter\Exception\Request\ForbiddenRequestException;
use Atymic\Twitter\Exception\Request\NotFoundException;
use Atymic\Twitter\Exception\Request\RateLimitedException;
use Atymic\Twitter\Exception\Request\UnauthorizedRequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\InvalidArgumentException as InvalidLogArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

abstract class Client implements ClientContract
{
    protected bool $debug;
    protected ?LoggerInterface $logger = null;

    public function __construct(bool $debug, ?LoggerInterface $logger)
    {
        $this->debug = $debug;
        $this->logger = $logger;
    }

    final protected function logRequest(
        string $method,
        string $url,
        array $data,
        string $logLevel = LogLevel::DEBUG
    ): void {
        try {
            if ($this->logger === null) {
                return;
            }

            $message = 'Making Request';
            $context = [
                'method' => $method,
                'query' => $url,
                'url' => $url,
                'params' => http_build_query($data),
            ];

            if (!$this->debug && $logLevel === LogLevel::DEBUG) {
                return;
            }

            $this->logger->log($logLevel, $message, $context);
        } catch (InvalidLogArgumentException $exception) {
            return;
        }
    }

    final protected function deduceClientException(Throwable $exception): TwitterClientException
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
                return new TwitterClientException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
