<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\TweetCounts;
use Exception;
use Prophecy\Argument;

final class TweetCountsTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testCountRecent(): void
    {
        $query = 'foobar';

        $this->querier->get(
            'tweets/counts/recent',
            Argument::that(
                fn (array $argument) => $argument['query'] === $query
            )
        )->shouldBeCalledTimes(1);

        $this->subject->countRecent($query);
    }

    /**
     * @throws Exception
     */
    public function testCountAll(): void
    {
        $query = 'foobar';

        $this->querier->get(
            'tweets/counts/all',
            Argument::that(
                fn (array $argument) => $argument['query'] === $query
            )
        )->shouldBeCalledTimes(1);

        $this->subject->countAll($query);
    }

    protected function getTraitName(): string
    {
        return TweetCounts::class;
    }
}
