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

    /*
    * getList can accept list_id, or slug and owner_screen_name, or slug and owner_id
    */
    public function testGetListWithId()
    {
        $this->paramTest('lists/show', 'getList', array(
            'list_id'=>1
        ));
    }

    public function testGetListWithSlugAndName()
    {
        $this->paramTest('lists/show', 'getList', array(
            'slug' => 'sugar_to_kiss',
            'owner_screen_name' => 'elwood'
        ));
    }

    public function testGetListWithSlugAndUserId()
    {
        $this->paramTest('lists/show', 'getList', array(
            'slug' => 'sugar_to_kiss',
            'owner_id' => 1
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testGetListInvalid()
    {
        $twitter = $this->getTwitter();

        $twitter->getList(array(
            'slug' => 'sweetheart_to_miss',
        ));
    }

    /*
    * getListMembers can accept list_id, or slug and owner_screen_name, or slug and owner_id
    */
    public function testGetListMembersWithId()
    {
        $this->paramTest('lists/members', 'getListMembers', array(
            'list_id' => 1
        ));
    }

    public function testGetListMembersWithSlugAndName()
    {
        $this->paramTest('lists/members', 'getListMembers', array(
            'slug' => 'sugar_to_kiss',
            'owner_screen_name' => 'elwood'
        ));
    }

    public function testGetListMembersWithSlugAndUserId()
    {
        $this->paramTest('lists/members', 'getListMembers', array(
            'slug' => 'sugar_to_kiss',
            'owner_id' => 1
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testGetListMembersInvalid()
    {
        $twitter = $this->getTwitter();

        $twitter->getListMembers(array(
            'slug' => 'sweetheart_to_miss',
        ));
    }

    /*
    * getListMember can accept list_id and user_id, or list_id and screen_name,
    * or slug and owner_screen_name and user_id, or slug and owner_screen_name and screen_name,
    * or slug and owner_id and user_id, or slug and owner_id and screen_name
    */
    public function testGetListMemberWithIdAndUserId()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'list_id' => 1,
            'user_id' => 2
        ));
    }

    public function testGetListMemberWithIdAndScreenName()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'list_id' => 1,
            'screen_name' => 'jake'
        ));
    }

    public function testGetListMemberWithSlugAndOwnerNameAndUserId()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'slug' => 'sugar_to_kiss',
            'owner_screen_name' => 'elwood',
            'user_id' => 2
        ));
    }

    public function testGetListMemberWithSlugAndOwnerNameAndScreenName()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'slug' => 'sugar_to_kiss',
            'owner_screen_name' => 'elwood',
            'screen_name' => 'jake'
        ));
    }

    public function testGetListMemberWithSlugAndOwnerIdAndScreenName()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'slug' => 'sugar_to_kiss',
            'owner_id' => 1,
            'screen_name' => 'jake'
        ));
    }

    public function testGetListMemberWithSlugAndOwnerIdAndUserId()
    {
        $this->paramTest('lists/members/show', 'getListMember', array(
            'slug' => 'sugar_to_kiss',
            'owner_id' => 1,
            'user_id' => 2
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testGetListMemberInvalid()
    {
        $twitter = $this->getTwitter();

        $twitter->getListMember(array(
            'slug' => 'sweetheart_to_miss',
        ));
    }
}
