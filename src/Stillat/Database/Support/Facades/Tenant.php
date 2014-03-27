<?php namespace Stillat\Database\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see  \Stillat\Database\Tenant\TenantManager
 */
class Tenant extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	public static function getFacadeAccessor()
	{
		return 'stillat.database.tenant';
	}

}