<?php namespace Thujohn\Twitter;

use Illuminate\Support\Facades\Facade;

class TwitterFacade extends Facade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'ttwitter';
	}

}