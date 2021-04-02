<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\HideReplies;
use Atymic\Twitter\Twitter;
use Exception;
use Prophecy\Argument;

final class HideRepliesTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testHideTweet(): void
    {
        $tweetId = '12342322155';
        $hidden = true;

        $this->querier->put(
            sprintf('tweets/%s/hidden', $tweetId),
            Argument::that(
                fn (array $argument) => $argument['hidden'] === $hidden
                    && $argument[Twitter::KEY_REQUEST_FORMAT] === Twitter::REQUEST_FORMAT_JSON
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $result = $this->subject->hideTweet($tweetId, $hidden);

        self::assertSame($result, self::ARBITRARY_RESPONSE);
    }

    protected function getTraitName(): string
    {
        return HideReplies::class;
    }
}
