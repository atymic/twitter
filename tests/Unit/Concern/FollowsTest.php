<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\Follows;
use Atymic\Twitter\Twitter;
use Exception;
use Prophecy\Argument;

final class FollowsTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testGetFollowing(): void
    {
        $userId = self::USER_ID;
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/%s/following', $userId), $params)
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $result = $this->subject->getFollowing($userId, $params);

        self::assertSame($result, self::ARBITRARY_RESPONSE);
    }

    /**
     * @throws Exception
     */
    public function testGetFollowers(): void
    {
        $userId = self::USER_ID;
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/%s/followers', $userId), $params)
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $result = $this->subject->getFollowers($userId, $params);

        self::assertSame($result, self::ARBITRARY_RESPONSE);
    }

    /**
     * @throws Exception
     */
    public function testFollow(): void
    {
        $userId = self::USER_ID;
        $targetUserId = '199999991';

        $this->querier->post(
            sprintf('users/%s/following', $userId),
            Argument::that(
                fn (array $argument) => $argument['target_user_id'] === $targetUserId
                    && $argument[Twitter::KEY_REQUEST_FORMAT] === Twitter::REQUEST_FORMAT_JSON
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $result = $this->subject->follow($userId, $targetUserId);

        self::assertSame($result, self::ARBITRARY_RESPONSE);
    }

    /**
     * @throws Exception
     */
    public function testUnfollow(): void
    {
        $userId = self::USER_ID;
        $targetUserId = '199999991';

        $this->querier->delete(sprintf('users/%s/following/%s', $userId, $targetUserId), ['response_format' => 'json'])
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $result = $this->subject->unfollow($userId, $targetUserId);

        self::assertSame($result, self::ARBITRARY_RESPONSE);
    }

    protected function getTraitName(): string
    {
        return Follows::class;
    }
}
