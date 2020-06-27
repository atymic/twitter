<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration\PhpDi;

use Atymic\Twitter\ServiceProviders\PhpDiTwitterServiceProvider;
use Atymic\Twitter\Tests\Integration\ResolutionTest;
use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class TwitterTest extends TestCase implements ResolutionTest
{
    /**
     * @var TwitterServiceProvider
     */
    private $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = new PhpDiTwitterServiceProvider();
    }

    public function testTwitterResolution(): void
    {
        $instance = $this->serviceProvider->resolve(Twitter::class);

        $this->assertInstanceOf(Twitter::class, $instance);
    }

    public function testTwitterResolutionViaAlias(): void
    {
        $instance = $this->serviceProvider->resolve(TwitterServiceProvider::PACKAGE_ALIAS);

        $this->assertInstanceOf(Twitter::class, $instance);
    }
}
