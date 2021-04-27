<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\ApiV1\Contract\Twitter as TwitterV1Contract;
use Atymic\Twitter\Concern\HotSwapper;
use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Twitter as TwitterV2Contract;
use Exception;
use Prophecy\Prophecy\ObjectProphecy;

final class HotSwapperTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testForApiV1(): void
    {
        /** @var Configuration|ObjectProphecy $v1Config */
        $v1Config = $this->prophesize(Configuration::class);
        $v1Config->getApiVersion()
            ->willReturn('1.1');

        $this->config->forApiV1()
            ->willReturn($v1Config->reveal());
        $this->config->forApiV2()
            ->shouldNotBeCalled();

        $this->querier->usingConfiguration($v1Config)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->querier->reveal());

        $result = $this->subject->forApiV1();

        self::assertInstanceOf(TwitterV1Contract::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testForApiV2(): void
    {
        /** @var Configuration|ObjectProphecy $v2Config */
        $v2Config = $this->prophesize(Configuration::class);
        $v2Config->getApiVersion()
            ->willReturn('2');

        $this->config->forApiV1()
            ->shouldNotBeCalled();
        $this->config->forApiV2()
            ->willReturn($v2Config->reveal());

        $this->querier->usingConfiguration($v2Config)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->querier->reveal());

        $result = $this->subject->forApiV2();

        self::assertInstanceOf(TwitterV2Contract::class, $result);
    }

    protected function getTraitName(): string
    {
        return HotSwapper::class;
    }
}
