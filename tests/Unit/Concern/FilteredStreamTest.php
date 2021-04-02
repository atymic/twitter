<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\FilteredStream;
use Atymic\Twitter\Exception\ClientException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;

/**
 * @property MockObject|FilteredStream
 */
final class FilteredStreamTest extends ConcernTestCase
{
    /**
     * @throws ClientException
     */
    public function testGetStream(): void
    {
        $onTweet = fn () => true;
        $params = ['foo' => 'bar'];

        $this->querier->getStream('tweets/search/stream', $onTweet, $params)
            ->shouldBeCalledTimes(1);

        $this->subject->getStream($onTweet, $params);
    }

    /**
     * @throws Exception
     */
    public function testGetStreamRules(): void
    {
        $params = self::ARBITRARY_PARAMS;
        $rules = [':foo' => 'bar rule'];

        $this->querier->get('tweets/search/stream/rules', $params)
            ->shouldBeCalledTimes(1)
            ->willReturn($rules);

        $result = $this->subject->getStreamRules($params);

        self::assertSame($rules, $result);
    }

    /**
     * @throws Exception
     */
    public function testPostStreamRules(): void
    {
        $params = self::ARBITRARY_PARAMS;
        $response = ['response'];

        $this->querier->post(
            'tweets/search/stream/rules',
            Argument::that(fn (array $argument): bool => $argument['foo'] === 'bar')
        )
            ->shouldBeCalledTimes(1)
            ->willReturn($response);

        $result = $this->subject->postStreamRules($params);

        self::assertSame($response, $result);
    }

    protected function getTraitName(): string
    {
        return FilteredStream::class;
    }
}
