<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration\Laravel;

use Atymic\Twitter\Tests\Integration\ResolutionTest;
use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider;
use Exception;

/**
 * @internal
 * @coversNothing
 */
final class TwitterTest extends TestCase implements ResolutionTest
{
    /**
     * @throws Exception
     */
    public function testTwitterResolution(): void
    {
        $this->assertInstanceOf(Twitter::class, app(Twitter::class));
    }

    /**
     * @throws Exception
     */
    public function testTwitterResolutionViaAlias(): void
    {
        $this->assertInstanceOf(Twitter::class, app(TwitterServiceProvider::PACKAGE_ALIAS));
    }
}
