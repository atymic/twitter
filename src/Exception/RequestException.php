<?php

declare(strict_types=1);

namespace Atymic\Twitter\Exception;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Throwable;

class RequestException extends RuntimeException implements TwitterException
{
    private const DEFAULT_ERROR_MESSAGE = 'An unknown request error occurred. See previous messages.';
    private const KEY_ERRORS = 'errors';
    private const KEY_CODE = 'code';
    private const KEY_MESSAGE = 'message';
    private const MESSAGE_FORMAT = '[%d] %s';

    /**
     * @var Response
     */
    protected $response;

    /**
     * @return static|TwitterException
     * @throws JsonException
     */
    public static function fromClientResponse(
        ResponseInterface $response,
        Throwable $previousException = null
    ): TwitterException {
        $responseStatusCode = $response->getStatusCode();
        $responseData = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $instance = new static(self::DEFAULT_ERROR_MESSAGE, $response->getStatusCode(), $previousException);

        if (empty($responseData[self::KEY_ERRORS])) {
            return $instance;
        }

        $error = $responseData[self::KEY_ERRORS][0];
        $errorCode = $error[self::KEY_CODE] ?? $responseStatusCode;
        $errorMessage = $error[self::KEY_MESSAGE] ?? self::DEFAULT_ERROR_MESSAGE;

        $instance->message = sprintf(self::MESSAGE_FORMAT, $errorCode, $errorMessage);
        $instance->code = $error[self::KEY_CODE] ?? $response->getStatusCode();

        return $instance;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
