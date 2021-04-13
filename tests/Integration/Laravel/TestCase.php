<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration\Laravel;

use Atymic\Twitter\ServiceProvider\LaravelServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Mockery\Exception\NoMatchingExpectationException;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    private const KEY_CONFIG = 'config';

    /**
     * @throws NoMatchingExpectationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTempDirectory());
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelServiceProvider::class];
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app[self::KEY_CONFIG]->set('mail.driver', 'log');

        $app[self::KEY_CONFIG]->set('database.default', 'sqlite');
        $app[self::KEY_CONFIG]->set(
            'database.connections.sqlite',
            [
                'driver' => 'sqlite',
                'database' => $this->getTempDirectory() . '/database.sqlite',
                'prefix' => '',
            ]
        );

        $app[self::KEY_CONFIG]->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param string $suffix
     */
    private function getTempDirectory($suffix = ''): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'temp' . ($suffix === '' ? '' : DIRECTORY_SEPARATOR . $suffix);
    }

    private function initializeDirectory(string $directory): bool
    {
        if (File::isDirectory($directory)) {
            return File::cleanDirectory($directory);
        }

        return File::makeDirectory($directory);
    }

    /**
     * @throws NoMatchingExpectationException
     */
    private function setUpDatabase(): void
    {
        file_put_contents($this->getTempDirectory() . '/database.sqlite', null);

        $this->artisan('migrate')->run();
    }
}
