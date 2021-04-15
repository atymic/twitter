<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http\Client;

use Atymic\Twitter\Contract\Http\SyncClient as SyncClientContract;
use Atymic\Twitter\Exception\ClientException;
use Atymic\Twitter\Http\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final class SyncClient extends Client implements SyncClientContract
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client, bool $debug = false, ?LoggerInterface $logger = null)
    {
        parent::__construct($debug, $logger);

        $this->client = $client;
    }

    /**
     * @return mixed
     * @throws ClientException
     */
    public function request(string $method, string $url, array $data = [])
    {
        try {
            $this->logRequest($method, $url, $data);

            $requestFormat = $data[self::KEY_REQUEST_FORMAT] ?? null;
            $responseFormat = $data[self::KEY_RESPONSE_FORMAT] ?? $data[self::KEY_FORMAT] ?? self::RESPONSE_FORMAT_OBJECT;

            unset(
                $data[self::KEY_REQUEST_FORMAT],
                $data[self::KEY_RESPONSE_FORMAT],
                $data[self::KEY_FORMAT]
            );

            $requestOptions = $this->getRequestOptions($method, $data, $requestFormat);

            return $this->formatResponse($this->client->request($method, $url, $requestOptions), $responseFormat);
        } catch (Throwable $exception) {
            throw $this->deduceClientException($exception);
        }
    }

    private function getRequestOptions(string $requestMethod, array $params, ?string $requestFormat): array
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

        $options[$paramsKey] = $params;

        return $options;
    }

    /**
     * @param Response|ResponseInterface $response
     *
     * @return mixed
     */
    private function formatResponse(ResponseInterface $response, string $format)
    {
        try {
            $body = $response->getBody();
            $content = (string) $body;

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
            if ($this->logger !== null) {
                $this->logger->error(
                    sprintf(
                        'A runtime exception occurred when formatting twitter response. %s',
                        $exception->getMessage()
                    )
                );
            }

            return null;
        } catch (JsonException $exception) {
            if ($this->logger !== null) {
                $this->logger->error(
                    sprintf('A JSON exception occurred when formatting twitter response. %s', $exception->getMessage())
                );
            }

            return null;
        }
    }
}
