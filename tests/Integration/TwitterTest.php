<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration;

use Atymic\Twitter\Twitter;
use Exception;

/**
 * @internal
 * @coversNothing
 */
final class TwitterTest extends TestCase
{
    /**
     * @var Twitter
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = app(Twitter::class);
    }

    /**
     * @throws Exception
     */
    public function testTwitterResolution(): void
    {
        $this->assertInstanceOf(Twitter::class, $this->subject);
    }

    /**
     * @throws Exception
     */
    public function testRequestExecution(): void
    {
        if (env('CI_TESTING')) {
            $this->markTestSkipped(
                'Valid Twitter oauth secrets are required for this test.'
                . ' We can revisit later with {ci secrets} :)'
            );
        }

        $expectedCount = 1;
        $screenName = 'IAmReliq';
        $tweets = $this->subject->getUserTimeline(['screen_name' => $screenName, 'count' => $expectedCount]);

        $this->assertCount($expectedCount, $tweets);

        foreach ($tweets as $tweet) {
            $this->assertIsObject($tweet);

            $htmlTweet = $this->subject->linkify($tweet->text);

            $this->assertIsString($htmlTweet);
        }
    }
}
