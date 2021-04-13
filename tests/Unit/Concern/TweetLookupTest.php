<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\TweetLookup;
use Exception;
use Prophecy\Argument;

final class TweetLookupTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testGetTweet(): void
    {
        $tweetId = '987654321';
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('tweets/%s', $tweetId), $params)
            ->shouldBeCalledTimes(1);

        $this->subject->getTweet($tweetId, $params);
    }

    /**
     * @throws Exception
     */
    public function testGetTweets(): void
    {
        $tweetId1 = '987654321';
        $tweetId2 = '123456789';
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(
            'tweets',
            Argument::that(
                fn (array $argument) => strpos($argument['ids'], $tweetId1) !== false
                    && strpos($argument['ids'], $tweetId2) !== false
            )
        )
            ->shouldBeCalledTimes(1);

        $this->subject->getTweets([$tweetId1, $tweetId2], $params);
    }

    protected function getTraitName(): string
    {
        return TweetLookup::class;
    }
}
