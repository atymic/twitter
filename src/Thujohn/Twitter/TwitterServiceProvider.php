<?php namespace Thujohn\Twitter;

use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('thujohn/twitter', 'thujohn/twitter');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['ttwitter'] = $this->app->share(function($app)
		{
			return new \Thujohn\Twitter\Twitter;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('ttwitter');
	}

}