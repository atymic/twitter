<?php

declare(strict_types=1);

namespace Thujohn\Twitter;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider dataLinkify
     */
    public function testLinkify(string $tweet, string $expected)
    {
        $this->assertSame($expected, Utils::linkify($tweet));
    }

    public function dataLinkify()
    {
        return [
            'username in middle' => [
                'hello @dave',
                'hello <a href=\"https://twitter.com/dave\" target=\"_blank\">@dave</a>',
            ],
            'username at start' => [
                '@dave sucks',
                '<a href=\"https://twitter.com/dave\" target=\"_blank\">@dave</a> sucks',
            ],
        ];
    }
}
