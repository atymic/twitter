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

    protected function getTwitterExpecting(array $queryParams)
    {
        $twitter = $this->getTwitter();
        $twitter->expects($this->once())
                ->method('query')
                ->with(
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    $queryParams
                );
        return $twitter;
    }

    public function testGetUsersWithScreenName()
    {
        $twitter = $this->getTwitterExpecting(array(
            'screen_name' => 'my_screen_name'
        ));

        $twitter->getUsers(array(
            'screen_name' => 'my_screen_name'
        ));
    }

    public function testGetUsersWithId()
    {
        $twitter = $this->getTwitterExpecting(array(
            'id' => 1234567890
        ));

        $twitter->getUsers(array(
            'id' => 1234567890
        ));
    }
}
