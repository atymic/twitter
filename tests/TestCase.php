<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests;

use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use File;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTempDirectory());
        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [TwitterServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'twitter' => Twitter::class,
        ];
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'temp' . ($suffix == '' ? '' : DIRECTORY_SEPARATOR . $suffix);
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory() . '/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory() . '/database.sqlite', null);

        $this->artisan('migrate')->run();
    }
}
