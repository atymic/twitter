<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\Timelines;
use Exception;

final class TimelinesTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testUserTweets(): void
    {
        $userId = self::USER_ID;
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/%s/tweets', $userId), $params)
            ->shouldBeCalledTimes(1);

        $this->subject->userTweets($userId, $params);
    }

    /**
     * @throws Exception
     */
    public function testUserMentions(): void
    {
        $userId = self::USER_ID;
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/%s/mentions', $userId), $params)
            ->shouldBeCalledTimes(1);

        $this->subject->userMentions($userId, $params);
    }

    protected function getTraitName(): string
    {
        return Timelines::class;
    }
}
