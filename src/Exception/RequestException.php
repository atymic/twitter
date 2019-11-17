<?php

declare(strict_types=1);

namespace Atymic\Twitter\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Throwable;

class RequestException extends RuntimeException implements TwitterException
{
    private const DEFAULT_ERROR_MESSAGE = 'An unknown error occurred';

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Response       $response
     * @param null|Throwable $previousException
     *
     * @return static|TwitterException
     */
    public static function fromClientResponse(
        ResponseInterface $response,
        Throwable $previousException = null
    ): TwitterException {
        $responseStatusCode = $response->getStatusCode();
        $responseData = json_decode((string)$response->getBody(), true);
        $self = new static(self::DEFAULT_ERROR_MESSAGE, $response->getStatusCode(), $previousException);

        if (empty($responseData['errors'])) {
            return $self;
        }

        $error = $responseData['errors'][0];
        $errorCode = $error['code'] ?? $responseStatusCode;
        $errorMessage = $error['message'] ?? self::DEFAULT_ERROR_MESSAGE;

        $self->message = sprintf('[%d] %s', $errorCode, $errorMessage);
        $self->code = $error['code'] ?? $response->getStatusCode();

        return $self;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
