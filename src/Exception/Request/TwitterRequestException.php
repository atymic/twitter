<?php
declare(strict_types=1);

namespace Atymic\Twitter\Exception\Request;

use Atymic\Twitter\Exception\TwitterException;
use Psr\Http\Message\ResponseInterface as Response;

class TwitterRequestException extends TwitterException
{
    /** @var Response */
    protected $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;

        $responseData = json_decode((string) $response->getBody(), true);

        if (!$responseData['errors'][0]) {
            $this->message = 'An unknown error occurred';
            $this->code = $response->getStatusCode();
            return;
        }

        $error = $responseData['errors'][0];

        $this->message = isset($error['message'])
            ? sprintf('[%d] %s', $error['code'], $error['message'])
            : 'An unknown error occured';

        $this->code = $error['code'] ?? $response->getStatusCode();
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
