<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Http\Client;

use Atymic\Twitter\Contract\Http\SyncClient as SyncClientContract;
use Atymic\Twitter\Contract\Twitter;
use Atymic\Twitter\Exception\ClientException;
use Atymic\Twitter\Http\Client\SyncClient;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use JsonException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * @coversDefaultClass \Atymic\Twitter\Http\Client\SyncClient
 */
final class SyncClientTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|ClientInterface
     */
    private ObjectProphecy $client;

    /**
     * @var ObjectProphecy|ResponseInterface
     */
    private ObjectProphecy $response;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private ObjectProphecy $logger;

    private SyncClientContract $subject;

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subject = new SyncClient($this->client->reveal(), false, $this->logger->reveal());

        $this->client->request(Argument::cetera())
            ->willReturn($this->response->reveal());

        $this->response->getBody()
            ->willReturn('{"foo": "bar"}');
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testRequest(): void
    {
        $method = 'POST';
        $url = '//url';
        $data = [];

        $result = $this->subject->request($method, $url, $data);

        self::assertInstanceOf(stdClass::class, $result);
        self::assertSame('bar', $result->foo);
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testJsonRequestJsonResponse(): void
    {
        $method = 'GET';
        $url = '//url';
        $data = [
            Twitter::KEY_REQUEST_FORMAT => Twitter::REQUEST_FORMAT_JSON,
            Twitter::KEY_RESPONSE_FORMAT => Twitter::RESPONSE_FORMAT_JSON,
            'key' => 'value',
        ];

        $this->client->request(
            $method,
            $url,
            Argument::that(
                fn (array $argument) => $argument[RequestOptions::JSON] === ['key' => 'value']
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($this->response->reveal());

        $result = $this->subject->request($method, $url, $data);

        self::assertJson($result);
        self::assertStringContainsString('foo', $result);
        self::assertStringContainsString('bar', $result);
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testMultipartRequestArrayResponse(): void
    {
        $method = 'POST';
        $url = '//url';
        $data = [
            Twitter::KEY_REQUEST_FORMAT => Twitter::REQUEST_FORMAT_MULTIPART,
            Twitter::KEY_RESPONSE_FORMAT => Twitter::RESPONSE_FORMAT_ARRAY,
            'field' => 'value',
        ];

        $this->client->request(
            $method,
            $url,
            Argument::that(
                fn (array $argument) => $argument[RequestOptions::MULTIPART] === ['field' => 'value']
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($this->response->reveal());

        $result = $this->subject->request($method, $url, $data);

        self::assertIsArray($result);
        self::assertSame('bar', $result['foo']);
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testRequestWhenRuntimeExceptionOccursInResponseFormatting(): void
    {
        $this->response->getBody()
            ->shouldBeCalledTimes(1)
            ->willThrow(new RuntimeException());

        $this->logger->error(Argument::cetera())
            ->shouldBeCalledTimes(1);

        $result = $this->subject->request('GET', '//url', []);

        self::assertNull($result);
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testRequestWhenJsonExceptionOccursInResponseFormatting(): void
    {
        $this->response->getBody()
            ->shouldBeCalledTimes(1)
            ->willThrow(new JsonException());

        $this->logger->error(Argument::cetera())
            ->shouldBeCalledTimes(1);

        $result = $this->subject->request('GET', '//url', []);

        self::assertNull($result);
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getRequestOptions
     * @covers ::formatResponse
     * @throws Throwable
     */
    public function testRequestWhenExceptionOccurs(): void
    {
        $this->response->getBody()
            ->shouldBeCalledTimes(1)
            ->willThrow(new Exception());

        $this->expectException(ClientException::class);

        $this->subject->request('GET', '//url', []);
    }
}
