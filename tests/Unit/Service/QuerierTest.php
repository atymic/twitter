<?php
/**
 * @noinspection PhpStrictTypeCheckingInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Service;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Http\AsyncClient;
use Atymic\Twitter\Contract\Http\ClientFactory;
use Atymic\Twitter\Contract\Http\SyncClient;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Service\Querier;
use Atymic\Twitter\Twitter;
use Exception;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Atymic\Twitter\Service\Querier
 */
final class QuerierTest extends TestCase
{
    use ProphecyTrait;

    private const API_URL = 'api.twitter.com';
    private const API_VERSION = '1.1';
    private const UPLOAD_URL = 'upload.twitter.com';

    /**
     * @var ObjectProphecy|Configuration
     */
    private ObjectProphecy $config;

    /**
     * @var ObjectProphecy|ClientFactory
     */
    private ObjectProphecy $clientFactory;

    /**
     * @var ObjectProphecy|SyncClient
     */
    private ObjectProphecy $syncClient;

    /**
     * @var ObjectProphecy|AsyncClient
     */
    private ObjectProphecy $asyncClient;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private ObjectProphecy $logger;

    private QuerierContract $subject;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->config = $this->prophesize(Configuration::class);
        $this->clientFactory = $this->prophesize(ClientFactory::class);
        $this->syncClient = $this->prophesize(SyncClient::class);
        $this->asyncClient = $this->prophesize(AsyncClient::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->config->getApiUrl()
            ->willReturn(self::API_URL);
        $this->config->getApiVersion()
            ->willReturn(self::API_VERSION);
        $this->config->getUploadUrl()
            ->willReturn(self::UPLOAD_URL);

        $this->clientFactory->createSyncClient($this->config->reveal())
            ->willReturn($this->syncClient->reveal());
        $this->clientFactory->createAsyncClient($this->config->reveal())
            ->willReturn($this->asyncClient->reveal());

        $this->subject = new Querier($this->config->reveal(), $this->clientFactory->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::directQuery
     * @throws Exception
     */
    public function testDirectQuery(): void
    {
        $url = '//url';
        $method = '//url';
        $params = [];
        $response = '{}';

        $this->syncClient->request($method, $url, $params)
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->directQuery($url, $method, $params);

        self::assertSame($response, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::query
     * @covers ::buildUrl
     * @throws Exception
     */
    public function testQuery(): void
    {
        $endpoint = 'endpoint';
        $method = 'GET';
        $params = [];
        $response = '{}';
        $multipart = true;
        $extension = 'ext';

        $this->syncClient->request(
            $method,
            Argument::that(
                fn (string $argument) => strpos(
                        $argument,
                        sprintf(
                            '%s/%s/%s.%s',
                            self::UPLOAD_URL,
                            self::API_VERSION,
                            $endpoint,
                            $extension
                        )
                    ) !== false
            ),
            Argument::that(fn (array $argument) => $argument[Twitter::KEY_REQUEST_FORMAT] === RequestOptions::MULTIPART)
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->query($endpoint, $method, $params, $multipart, $extension);

        self::assertSame($response, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::query
     * @covers ::buildUrl
     * @throws Exception
     */
    public function testGet(): void
    {
        $endpoint = 'endpoint';
        $params = [];
        $response = '{}';
        $extension = 'ext';

        $this->syncClient->request(
            'GET',
            Argument::that(
                fn (string $argument) => strpos(
                        $argument,
                        sprintf(
                            '%s/%s/%s.%s',
                            self::API_URL,
                            self::API_VERSION,
                            $endpoint,
                            $extension
                        )
                    ) !== false
            ),
            $params
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->get($endpoint, $params, $extension);

        self::assertSame($response, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::post
     * @covers ::query
     * @covers ::buildUrl
     * @throws Exception
     */
    public function testPost(): void
    {
        $endpoint = 'endpoint';
        $params = [];
        $response = '{}';

        $this->syncClient->request(
            'POST',
            Argument::that(
                fn (string $argument) => strpos(
                        $argument,
                        sprintf(
                            '%s/%s/%s',
                            self::API_URL,
                            self::API_VERSION,
                            $endpoint
                        )
                    ) !== false
            ),
            $params
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->post($endpoint, $params);

        self::assertSame($response, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::put
     * @covers ::query
     * @covers ::buildUrl
     * @throws Exception
     */
    public function testPut(): void
    {
        $endpoint = 'endpoint';
        $params = [];
        $response = '{}';

        $this->syncClient->request(
            'PUT',
            Argument::that(
                fn (string $argument) => strpos(
                        $argument,
                        sprintf(
                            '%s/%s/%s',
                            self::API_URL,
                            self::API_VERSION,
                            $endpoint
                        )
                    ) !== false
            ),
            $params
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->put($endpoint, $params);

        self::assertSame($response, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     * @covers ::query
     * @covers ::buildUrl
     * @throws Exception
     */
    public function testDelete(): void
    {
        $endpoint = 'endpoint';
        $params = [];
        $response = '{}';

        $this->syncClient->request(
            'DELETE',
            Argument::that(
                fn (string $argument) => strpos(
                        $argument,
                        sprintf(
                            '%s/%s/%s',
                            self::API_URL,
                            self::API_VERSION,
                            $endpoint
                        )
                    ) !== false
            ),
            $params
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->delete($endpoint, $params);

        self::assertSame($response, $result);
    }
}
