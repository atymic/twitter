<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Service;

use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Twitter;
use Atymic\Twitter\Service\Accessor;
use Atymic\Twitter\Tests\Unit\AccessorTestCase;
use Exception;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Atymic\Twitter\Service\Accessor
 */
final class AccessorTest extends AccessorTestCase
{
    use ProphecyTrait;

    private Twitter $subject;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Accessor($this->querier->reveal());
    }

    /**
     * @covers ::usingCredentials
     * @covers ::__construct
     * @covers ::getQuerier
     * @throws Exception
     */
    public function testUsingCredentials(): void
    {
        $accessToken = 'token';
        $accessTokenSecret = 'secret';

        $result = $this->subject
            ->usingCredentials($accessToken, $accessTokenSecret);

        self::assertInstanceOf(Twitter::class, $result);
        self::assertNotSame($result, $this->subject);
    }

    /**
     * @covers ::usingConfiguration
     * @covers ::__construct
     * @covers ::getQuerier
     * @throws Exception
     */
    public function testUsingConfiguration(): void
    {
        /**
         * @var Configuration $config
         */
        $config = $this->prophesize(Configuration::class)
            ->reveal();

        $result = $this->subject
            ->usingConfiguration($config);

        self::assertInstanceOf(Twitter::class, $result);
        self::assertNotSame($result, $this->subject);
    }
}
