<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit;

use Atymic\Twitter\Twitter;
use BadMethodCallException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Class TwitterTest.
 *
 * @coversDefaultClass \Atymic\Twitter\Twitter
 *
 * @internal
 */
final class TwitterTest extends TestCase
{
    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersWithScreenName(): void
    {
        $twitter = $this->getTwitterExpecting('users/show', [
            'screen_name' => 'my_screen_name',
        ]);

        $twitter->getUsers([
            'screen_name' => 'my_screen_name',
        ]);
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersWithId(): void
    {
        $twitter = $this->getTwitterExpecting('users/show', [
            'user_id' => 1234567890,
        ]);

        $twitter->getUsers([
            'user_id' => 1234567890,
        ]);
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsers([
            'include_entities' => true,
        ]);
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupWithIds(): void
    {
        $twitter = $this->getTwitterExpecting('users/lookup', [
            'user_id' => '1,2,3,4',
        ]);

        $twitter->getUsersLookup([
            'user_id' => implode(',', [1, 2, 3, 4]),
        ]);
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupWithScreenNames(): void
    {
        $twitter = $this->getTwitterExpecting('users/lookup', [
            'screen_name' => 'me,you,everybody',
        ]);

        $twitter->getUsersLookup([
            'screen_name' => implode(',', ['me', 'you', 'everybody']),
        ]);
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsersLookup([
            'include_entities' => true,
        ]);
    }

    /**
     * getList can accept list_id, or slug and owner_screen_name, or slug and owner_id.
     *
     * Use a Data Provider to test this method with different params without repeating our code
     *
     * @dataProvider providerGetList
     *
     * @param array $params
     *
     * @throws RuntimeException
     */
    public function testGetList(array $params): void
    {
        $this->paramTest('lists/show', 'getList', $params);
    }

    /**
     * @return array
     */
    public function providerGetList(): array
    {
        return [
            [
                ['list_id' => 1],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListBad
     *
     * @param array $params
     *
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getList($params);
    }

    /**
     * @return array
     */
    public function providerGetListBad(): array
    {
        return [
            [
                ['slug' => 1],
            ],
        ];
    }

    /**
     * getListMembers can accept list_id, or slug and owner_screen_name, or slug and owner_id.
     *
     * @dataProvider providerGetListMembers
     *
     * @param array $params
     *
     * @throws RuntimeException
     */
    public function testGetListMembers(array $params): void
    {
        $this->paramTest('lists/members', 'getListMembers', $params);
    }

    /**
     * @return array
     */
    public function providerGetListMembers(): array
    {
        return [
            [
                ['list_id' => 1],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListMembersBad
     *
     * @param array $params
     *
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListMembersFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getListMembers($params);
    }

    /**
     * @return array
     */
    public function providerGetListMembersBad(): array
    {
        return [
            [
                ['slug' => 'sweetheart_to_miss'],
            ],
        ];
    }

    /**
     * getListMember can accept list_id and user_id, or list_id and screen_name,
     * or slug and owner_screen_name and user_id, or slug and owner_screen_name and screen_name,
     * or slug and owner_id and user_id, or slug and owner_id and screen_name.
     *
     * @dataProvider providerGetListMember
     *
     * @param array $params
     *
     * @throws RuntimeException
     */
    public function testGetListMember(array $params): void
    {
        $this->paramTest('lists/members/show', 'getListMember', $params);
    }

    /**
     * @return array
     */
    public function providerGetListMember(): array
    {
        return [
            [
                ['list_id' => 1, 'user_id' => 2],
            ],
            [
                ['list_id' => 1, 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'user_id' => 2],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1, 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1, 'user_id' => 2],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListMemberBad
     *
     * @param array $params
     *
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListMemberFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();
        $twitter->getListMembers($params);
    }

    /**
     * @return array
     */
    public function providerGetListMemberBad(): array
    {
        return [
            [
                ['slug' => 'sweetheart_to_miss'],
            ],
        ];
    }

    /**
     * @throws RuntimeException
     *
     * @return MockObject|Twitter
     */
    protected function getTwitter(): MockObject
    {
        return $this->getMockBuilder(Twitter::class)
            ->onlyMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $endpoint
     * @param array  $queryParams
     *
     * @throws RuntimeException
     *
     * @return MockObject|Twitter
     */
    protected function getTwitterExpecting(string $endpoint, array $queryParams): MockObject
    {
        $twitter = $this->getTwitter();
        $twitter->expects($this->once())
            ->method('query')
            ->with(
                $endpoint,
                $this->anything(),
                $queryParams
            );

        return $twitter;
    }

    /**
     * @param string $endpoint
     * @param string $testedMethod
     * @param array  $params
     *
     * @throws RuntimeException
     */
    private function paramTest(string $endpoint, string $testedMethod, array $params)
    {
        $twitter = $this->getTwitterExpecting($endpoint, $params);

        $twitter->{$testedMethod}($params);
    }
}
