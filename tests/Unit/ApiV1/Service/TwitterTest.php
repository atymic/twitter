<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\ApiV1\Service;

use Atymic\Twitter\ApiV1\Service\Twitter;
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
        $twitter = $this->getTwitterExpectingQueryCall(
            'users/show',
            [
                'screen_name' => 'my_screen_name',
            ]
        );

        $twitter->getUsers(
            [
                'screen_name' => 'my_screen_name',
            ]
        );
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersWithId(): void
    {
        $twitter = $this->getTwitterExpectingQueryCall(
            'users/show',
            [
                'user_id' => 1234567890,
            ]
        );

        $twitter->getUsers(
            [
                'user_id' => 1234567890,
            ]
        );
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsers(
            [
                'include_entities' => true,
            ]
        );
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupWithIds(): void
    {
        $twitter = $this->getTwitterExpectingQueryCall(
            'users/lookup',
            [
                'user_id' => '1,2,3,4',
            ]
        );

        $twitter->getUsersLookup(
            [
                'user_id' => implode(',', [1, 2, 3, 4]),
            ]
        );
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupWithScreenNames(): void
    {
        $twitter = $this->getTwitterExpectingQueryCall(
            'users/lookup',
            [
                'screen_name' => 'me,you,everybody',
            ]
        );

        $twitter->getUsersLookup(
            [
                'screen_name' => implode(',', ['me', 'you', 'everybody']),
            ]
        );
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetUsersLookupInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsersLookup(
            [
                'include_entities' => true,
            ]
        );
    }

    /**
     * getList can accept list_id, or slug and owner_screen_name, or slug and owner_id.
     *
     * Use a Data Provider to test this method with different params without repeating our code
     *
     * @dataProvider providerGetList
     *
     * @throws RuntimeException
     */
    public function testGetList(array $params): void
    {
        $this->paramTest('lists/show', 'getList', $params);
    }

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
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getList($params);
    }

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
     * @throws RuntimeException
     */
    public function testGetListMembers(array $params): void
    {
        $this->paramTest('lists/members', 'getListMembers', $params);
    }

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
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListMembersFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getListMembers($params);
    }

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
     * @throws RuntimeException
     */
    public function testGetListMember(array $params): void
    {
        $this->paramTest('lists/members/show', 'getListMember', $params);
    }

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
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetListMemberFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();
        $twitter->getListMembers($params);
    }

    public function providerGetListMemberBad(): array
    {
        return [
            [
                ['slug' => 'sweetheart_to_miss'],
            ],
        ];
    }

    /**
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function testGetOembed(): void
    {
        $twitter = $this->getTwitter();

        $twitter->expects(self::once())
            ->method('directQuery')
            ->with(
                'https://publish.twitter.com/oembed',
                'GET',
                ['url' => 'https://twitter.com/jxeeno/status/1343506068236689408']
            );

        $twitter->getOembed(['url' => 'https://twitter.com/jxeeno/status/1343506068236689408']);
    }

    /**
     * @return MockObject|Twitter
     * @throws Exception
     */
    protected function getTwitter(): MockObject
    {
        return $this->getMockBuilder(Twitter::class)
            ->onlyMethods(['query', 'directQuery'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|Twitter
     * @throws Exception
     */
    protected function getTwitterExpectingQueryCall(string $endpoint, array $queryParams): MockObject
    {
        $twitter = $this->getTwitter();
        $twitter->expects(self::once())
            ->method('query')
            ->with(
                $endpoint,
                self::anything(),
                $queryParams
            );

        return $twitter;
    }

    /**
     * @throws RuntimeException
     */
    private function paramTest(string $endpoint, string $testedMethod, array $params): void
    {
        $twitter = $this->getTwitterExpectingQueryCall($endpoint, $params);

        $twitter->{$testedMethod}($params);
    }
}
