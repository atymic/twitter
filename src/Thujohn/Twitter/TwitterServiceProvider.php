<?php namespace Thujohn\Twitter;

use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$configPath = __DIR__ . '/../../config/thujohn.twitter.php';
		$this->mergeConfigFrom( $configPath, 'ttwitter' );
		$this->publishes( [ $configPath => config_path( 'ttwitter.php' ) ] );

		$this->app['ttwitter'] = $this->app->share( function ()
		{
			return new \Thujohn\Twitter\Twitter;
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [ 'ttwitter' ];
	}

}
