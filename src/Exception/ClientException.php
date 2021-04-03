<?php

declare(strict_types=1);

namespace Atymic\Twitter\Exception;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class ClientException extends RuntimeException implements TwitterException
{
    private const DEFAULT_ERROR_MESSAGE_FORMAT = 'A request error occurred. %s';
    private const KEY_ERRORS = 'errors';
    private const KEY_CODE = 'code';
    private const KEY_MESSAGE = 'message';
    private const MESSAGE_FORMAT = '[%d] %s';

    protected ?Response $response = null;

    /**
     * @return static|TwitterException
     */
    public static function fromClientResponse(
        ResponseInterface $response,
        Throwable $previousException = null
    ): TwitterException {
        $responseStatusCode = $response->getStatusCode();
        try {
            $responseData = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
        }

        $errorMessage = sprintf(
            self::DEFAULT_ERROR_MESSAGE_FORMAT,
            $previousException !== null ? $previousException->getMessage() : ''
        );
        $instance = new static(
            $errorMessage,
            $response->getStatusCode(),
            $previousException
        );
        $instance->response = $response;

        if (empty($responseData[self::KEY_ERRORS])) {
            return $instance;
        }

        $error = $responseData[self::KEY_ERRORS][0];
        $errorCode = $error[self::KEY_CODE] ?? $responseStatusCode;

        $instance->message = sprintf(self::MESSAGE_FORMAT, $errorCode, $error[self::KEY_MESSAGE] ?? $errorMessage);
        $instance->code = $error[self::KEY_CODE] ?? $response->getStatusCode();

        return $instance;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
