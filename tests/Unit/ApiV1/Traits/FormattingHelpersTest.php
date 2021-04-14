<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\ApiV1\Traits;

use Atymic\Twitter\ApiV1\Traits\FormattingHelpers;

final class FormattingHelpersTest extends ConcernTestCase
{
    /**
     * @dataProvider dataGetUserLink
     */
    public function testGetUserLink($user): void
    {
        $this->assertSame(
            'https://twitter.com/atymic',
            $this->subject->linkUser($user)
        );
    }

    public function dataGetUserLink(): array
    {
        return [
            'string' =>  ['atymic'],
            'object' =>  [(object) ['screen_name' => 'atymic']],
            'array' =>  [['screen_name' => 'atymic']],
        ];
    }

    /**
     * @dataProvider dataLinkAddTweetToFavorites
     */
    public function testLinkAddTweetToFavorites($tweet): void
    {
        $this->assertSame(
            'https://twitter.com/intent/favorite?tweet_id=1381031025053155332',
            $this->subject->linkAddTweetToFavorites($tweet)
        );
    }

    public function dataLinkAddTweetToFavorites(): array
    {
        return [
            'object' =>  [(object) ['id_str' => '1381031025053155332']],
            'array' =>  [['id_str' => '1381031025053155332']],
        ];
    }

    protected function getTraitName(): string
    {
        return FormattingHelpers::class;
    }
}
