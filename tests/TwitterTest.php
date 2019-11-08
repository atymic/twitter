<?php
declare(strict_types=1);

namespace Atymic\Twitter\Tests;

use Atymic\Twitter\Twitter;

class TwitterTest extends TestCase
{
    public function testGetsTwitterInstance()
    {
        $twitter = app(Twitter::class);

        $this->assertInstanceOf(Twitter::class, $twitter);
    }

    public function testActualRequest()
    {
        /** @var Twitter $twitter */
        $twitter = app(Twitter::class);

        $twitter->getSettings();
    }
}
