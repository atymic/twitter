<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\UserLookup;
use Exception;
use Prophecy\Argument;

final class UserLookupTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testGetUser(): void
    {
        $userId = self::USER_ID;
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/%s', $userId), $params)
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $this->subject->getUser($userId, $params);
    }

    /**
     * @throws Exception
     */
    public function testGetUsers(): void
    {
        $userId1 = self::USER_ID;
        $userId2 = '32452123';
        $userIds = [$userId1, $userId2];
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(
            'users',
            Argument::that(
                fn (array $argument) => strpos($argument['ids'], $userId1) !== false
                    && strpos($argument['ids'], $userId2) !== false
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $this->subject->getUsers($userIds, $params);
    }

    /**
     * @throws Exception
     */
    public function testGetUserByUsername(): void
    {
        $username = 'user';
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(sprintf('users/by/username/%s', $username), $params)
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $this->subject->getUserByUsername($username, $params);
    }

    /**
     * @throws Exception
     */
    public function testGetUsersByUsernames(): void
    {
        $username1 = 'user1';
        $username2 = 'user2';
        $params = self::ARBITRARY_PARAMS;

        $this->querier->get(
            'users/by',
            Argument::that(
                fn (array $argument) => strpos($argument['usernames'], $username1) !== false
                    && strpos($argument['usernames'], $username2) !== false
            )
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(self::ARBITRARY_RESPONSE);

        $this->subject->getUsersByUsernames([$username1, $username2], $params);
    }

    protected function getTraitName(): string
    {
        return UserLookup::class;
    }
}
