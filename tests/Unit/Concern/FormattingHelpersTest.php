<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\ApiV1\Traits\FormattingHelpers;
use Atymic\Twitter\Concern\HideReplies;
use Atymic\Twitter\Twitter;
use Exception;
use Prophecy\Argument;

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
          'object' =>  [(object) ['user' => 'screen_name']],
          'arrat' =>  [['user' => 'screen_name']],
        ];
    }



    protected function getTraitName(): string
    {
        return FormattingHelpers::class;
    }
}
