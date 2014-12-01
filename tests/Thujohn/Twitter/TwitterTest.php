<?php


class TwitterTest extends \PHPUnit_Framework_TestCase
{
    protected function getTwitter()
    {
        return $this->getMockBuilder('Thujohn\Twitter\Twitter')
                    ->setMethods(array('query'))
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    protected function getTwitterExpecting($endpoint, array $queryParams)
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

    public function paramTest($endpoint, $testedMethod, $params)
    {
        $twitter = $this->getTwitterExpecting($endpoint, $params);

        $twitter->$testedMethod($params);
    }

    public function testGetUsersWithScreenName()
    {
        $twitter = $this->getTwitterExpecting('users/show', array(
            'screen_name' => 'my_screen_name'
        ));

        $twitter->getUsers(array(
            'screen_name' => 'my_screen_name'
        ));
    }

    public function testGetUsersWithId()
    {
        $twitter = $this->getTwitterExpecting('users/show', array(
            'user_id' => 1234567890
        ));

        $twitter->getUsers(array(
            'user_id' => 1234567890
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testGetUsersInvalid()
    {
        $twitter = $this->getTwitter();

        $twitter->getUsers(array(
            'include_entities' => true
        ));
    }

    public function testGetUsersLookupWithIds()
    {
        $twitter = $this->getTwitterExpecting('users/lookup', array(
            'user_id' => '1,2,3,4'
        ));

        $twitter->getUsersLookup(array(
            'user_id' => implode(',', array(1, 2, 3, 4))
        ));
    }

    public function testGetUsersLookupWithScreenNames()
    {
        $twitter = $this->getTwitterExpecting('users/lookup', array(
            'screen_name' => 'me,you,everybody'
        ));

        $twitter->getUsersLookup(array(
            'screen_name' => implode(',', array('me', 'you', 'everybody'))
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testGetUsersLookupInvalid()
    {
        $twitter = $this->getTwitter();

        $twitter->getUsersLookup(array(
            'include_entities' => true
        ));
    }

    /**
    * getList can accept list_id, or slug and owner_screen_name, or slug and owner_id
    *
    * Use a Data Provider to test this method with different params without repeating our code
    * @dataProvider providerGetList
    */
    public function testGetList($params)
    {
        $this->paramTest('lists/show', 'getList', $params);
    }

    public function providerGetList()
    {
        return array(
            array(
                array('list_id'=>1),
            ),
            array(
                array('slug'=>'sugar_to_kiss', 'owner_screen_name'=>'elwood')
            ),
            array(
                array('slug'=>'sugar_to_kiss', 'owner_id'=>1)
            )
        );
    }

    /**
    *
    * @dataProvider providerGetListBad
    */
    public function testGetListFails($params)
    {
        $this->setExpectedException('Exception');
        $twitter = $this->getTwitter();
        $twitter->getList($params);
    }

    public function providerGetListBad()
    {
        return array(
            array(
                array('slug'=>1),
            )
        );
    }

    /**
    * getListMembers can accept list_id, or slug and owner_screen_name, or slug and owner_id
    *
    * @dataProvider providerGetListMembers
    */
    public function testGetListMembers($params)
    {
        $this->paramTest('lists/members', 'getListMembers', $params);
    }

    public function providerGetListMembers()
    {
        return array(
            array(
                array('list_id'=>1),
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood'),
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_id' => 1),
            )
        );
    }

    /**
    * @dataProvider providerGetListMembersBad
    */
    public function testGetListMembersFails($params)
    {
        $this->setExpectedException('Exception');
        $twitter = $this->getTwitter();
        $twitter->getListMembers($params);
    }

    public function providerGetListMembersBad()
    {
        return array(
            array(
                array('slug' => 'sweetheart_to_miss')
            )
        );
    }

    /**
    * getListMember can accept list_id and user_id, or list_id and screen_name,
    * or slug and owner_screen_name and user_id, or slug and owner_screen_name and screen_name,
    * or slug and owner_id and user_id, or slug and owner_id and screen_name
    *
    * @dataProvider providerGetListMember
    */
    public function testGetListMember($params)
    {
        $this->paramTest('lists/members/show', 'getListMember', $params);
    }

    public function providerGetListMember()
    {
        return array(
            array(
                array('list_id' => 1, 'user_id' => 2)
            ),
            array(
                array('list_id' => 1, 'screen_name' => 'jake')
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'user_id' => 2)
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'screen_name' => 'jake')
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_id' => 1, 'screen_name' => 'jake')
            ),
            array(
                array('slug' => 'sugar_to_kiss', 'owner_id' => 1, 'user_id' => 2)
            )
        );
    }

    /**
    * @dataProvider providerGetListMemberBad
    */
    public function testGetListMemberFails($params)
    {
        $this->setExpectedException('Exception');
        $twitter = $this->getTwitter();
        $twitter->getListMembers($params);
    }

    public function providerGetListMemberBad()
    {
        return array(
            array(
                array('slug' => 'sweetheart_to_miss')
            )
        );
    }

}
