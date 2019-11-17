<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration;

use Atymic\Twitter\Twitter;

/**
 * @internal
 * @coversNothing
 */
final class TwitterTest extends TestCase
{
    public function testTwitterResolution(): void
    {
        $twitter = app(Twitter::class);

        $this->assertInstanceOf(Twitter::class, $twitter);
    }

    public function testActualRequest(): void
    {
        $this->markTestSkipped('For future reference.');

        /** @var Twitter $twitter */
        $twitter = app(Twitter::class);

        $twitter->getSettings();
    }
}
