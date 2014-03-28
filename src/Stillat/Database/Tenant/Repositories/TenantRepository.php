<?php namespace Stillat\Database\Tenant\Repositories;

use Stillat\Database\Tenant\TenantManager;
use Stillat\Database\Repositories\BaseRepository;

abstract class TenantRepository extends BaseRepository {

	/**
	 * Returns a new instance of TenantRepository
	 */
	public function __construct()
	{
		$tenantManager = TenantManager::instance();
		$this->setConnection($tenantManager->getCurrentConnection());
		unset($tenantManager);
	}

}