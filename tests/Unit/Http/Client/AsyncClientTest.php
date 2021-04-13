<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Http\Client;

use Atymic\Twitter\Contract\Http\AsyncClient as AsyncClientContact;
use Atymic\Twitter\Exception\TwitterException;
use Atymic\Twitter\Http\Client\AsyncClient;
use Atymic\Twitter\Http\Factory\BrowserCreator;
use Atymic\Twitter\Http\OAuth2Provider;
use Atymic\Twitter\Twitter;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;

/**
 * @coversDefaultClass \Atymic\Twitter\Http\Client\AsyncClient
 */
final class AsyncClientTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|BrowserCreator
     */
    private ObjectProphecy $browserCreator;

    /**
     * @var ObjectProphecy|OAuth2Provider
     */
    private ObjectProphecy $oAuth2Provider;

    /**
     * @var ObjectProphecy|LoopInterface
     */
    private ObjectProphecy $loop;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private ObjectProphecy $logger;

    private PromiseInterface $promise;

    private AsyncClientContact $subject;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->browserCreator = $this->prophesize(BrowserCreator::class);
        $this->oAuth2Provider = $this->prophesize(OAuth2Provider::class);
        $this->loop = $this->prophesize(LoopInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->promise = $this->prophesize(PromiseInterface::class)
            ->reveal();
        $this->subject = new AsyncClient(
            $this->browserCreator->reveal(),
            $this->oAuth2Provider->reveal(),
            true,
            $this->loop->reveal(),
            $this->logger->reveal()
        );

        /**
         * @var ObjectProphecy|AccessTokenInterface $accessToken
         * @var ObjectProphecy|Browser $browser
         */
        $accessToken = $this->prophesize(AccessTokenInterface::class);
        $browser = $this->prophesize(Browser::class);

        $accessToken->__toString()
            ->willReturn('ACCESS_TOKEN');

        $browser->request(Argument::cetera())
            ->willReturn($this->promise);
        $browser->requestStreaming(Argument::cetera())
            ->willReturn($this->promise);

        $this->browserCreator
            ->create(Argument::type(LoopInterface::class))
            ->willReturn($browser);

        $this->oAuth2Provider
            ->getAccessToken(Argument::cetera())
            ->willReturn($accessToken->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getAccessToken
     * @covers ::getAuthHeader
     * @covers ::getBrowser
     * @covers ::getQueryParams
     * @covers \Atymic\Twitter\Http\Client::__construct
     * @covers \Atymic\Twitter\Http\Client::logRequest
     * @throws Exception
     */
    public function testRequest(): void
    {
        self::assertSame(
            $this->promise,
            $this->subject->request(
                'GET',
                '//url',
                '',
                [
                    Twitter::KEY_STREAM_STOP_AFTER_SECONDS => 3,
                ]
            )
        );
    }

    /**
     * @covers ::__construct
     * @covers ::request
     * @covers ::getAccessToken
     * @covers ::getAuthHeader
     * @covers \Atymic\Twitter\Http\Client::__construct
     * @covers \Atymic\Twitter\Http\Client::logRequest
     * @covers \Atymic\Twitter\Http\Client::deduceClientException
     * @throws Exception
     */
    public function testRequestWhenExceptionOccurs(): void
    {
        $this->oAuth2Provider
            ->getAccessToken(Argument::cetera())
            ->willThrow(new Exception('foo'));

        $this->expectException(TwitterException::class);

        $this->subject->request('POST', '//url');
    }

    /**
     * @covers ::__construct
     * @covers ::stream
     * @covers ::getAccessToken
     * @covers ::getAuthHeader
     * @covers ::getBrowser
     * @covers ::getQueryParams
     * @covers \Atymic\Twitter\Http\Client::__construct
     * @covers \Atymic\Twitter\Http\Client::logRequest
     * @throws Exception
     */
    public function testStream(): void
    {
        self::assertSame(
            $this->promise,
            $this->subject->stream(
                'GET',
                '//url',
                [
                    Twitter::KEY_STREAM_STOP_AFTER_SECONDS => 3,
                ]
            )
        );
    }

    /**
     * @covers ::__construct
     * @covers ::stream
     * @covers ::getAccessToken
     * @covers ::getAuthHeader
     * @covers \Atymic\Twitter\Http\Client::__construct
     * @covers \Atymic\Twitter\Http\Client::logRequest
     * @covers \Atymic\Twitter\Http\Client::deduceClientException
     * @throws Exception
     */
    public function testStreamWhenExceptionOccurs(): void
    {
        $this->oAuth2Provider
            ->getAccessToken(Argument::cetera())
            ->willThrow(new IdentityProviderException('foo', 400, ''));

        $this->expectException(TwitterException::class);

        $this->subject->stream('POST', '//url');
    }

    /**
     * @covers ::loop
     */
    public function testLoop()
    {
        self::assertSame($this->loop->reveal(), $this->subject->loop());
    }
}
