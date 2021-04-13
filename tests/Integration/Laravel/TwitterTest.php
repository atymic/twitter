<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration\Laravel;

use Atymic\Twitter\Contract\ServiceProvider;
use Atymic\Twitter\Tests\Integration\ResolutionTest;
use Atymic\Twitter\Twitter;
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
        self::assertInstanceOf(Twitter::class, app(Twitter::class));
    }

    /**
     * @throws Exception
     */
    public function testTwitterResolutionViaAlias(): void
    {
        self::assertInstanceOf(Twitter::class, app(ServiceProvider::PACKAGE_ALIAS));
    }
}
