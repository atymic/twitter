<?php namespace Thujohn\Twitter;

use Illuminate\Support\ServiceProvider;
use Thujohn\Twitter\Twitter;

class TwitterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;
	protected $isLumen = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app ?: app();
		$laravel_version = substr($app->version(), 0, strpos($app->version(), '.'));

		if (strpos(strtolower($laravel_version), 'lumen') !== false)
		{
			$this->isLumen = true;
			$laravel_version = str_replace('Lumen (', '', $laravel_version);
		}

		if ($laravel_version == 5)
		{
			$this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'ttwitter');
			if ($this->isLumen)
			{
				$this->publishes([
					__DIR__ . '/../../config/config.php' => app()->basePath()
						. '/config' . ('ttwitter.php' ? '/' . 'ttwitter.php' : 'ttwitter.php'),
				]);
			}
			else
			{
				$this->publishes([
					__DIR__ . '/../../config/config.php' => config_path('ttwitter.php'),
				]);
			}
		}
		else if ($laravel_version == 4)
		{
			$this->package('thujohn/twitter', 'ttwitter', __DIR__.'/../..');
		}

		$this->app['ttwitter'] = $this->app->share(function($app)
		{
			return new Twitter($app['config'], $app['session.store']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['ttwitter'];
	}

}
