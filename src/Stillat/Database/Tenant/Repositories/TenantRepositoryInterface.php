<?php namespace Stillat\Database\Tenant\Repositories;

interface TenantRepositoryInterface {

	/**
	 * Gets a specific tenant by ID.
	 * 
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function getTenant($tenantID);

	/**
	 * Creates a log for a given tenant.
	 * 
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function log($tenantID);

	/**
	 * Gets all tenants.
	 * 
	 * @return array
	 */
	public function getTenants();

	/**
	 * Gives a user permission on a tenant.
	 * 
	 * @param  int   $userID
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function grantUserOnTenant($userID, $tenantID);

	/**
	 * Gets all tenants for a user.
	 * 
	 * @param  int   $userID
	 * @return array
	 */
	public function getUserTenants($userID);

}