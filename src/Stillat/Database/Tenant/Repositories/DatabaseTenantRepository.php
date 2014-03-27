<?php namespace Stillat\Database\Tenant\Repositories;

use Illuminate\Support\Facades\DB;
use Stillat\Database\Repositories\BaseRepository;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Stillat\Database\Tenant\Repositories\TenantRepositoryInterface;

class DatabaseTenantRepository extends BaseRepository implements TenantRepositoryInterface {

	/**
	 * The database connection resolver instance.
	 *
	 * @var \Illuminate\Database\ConnectionResolverInterface
	 */
	protected $resolver;

	/**
	 * The name of the tenant account table.
	 * 
	 * @var string
	 */
	protected $tenantAccountTable = '';

	/**
	 * Returns a new instance of DatabaseTenantRepository.
	 * 
	 * @param \Illuminate\Database\ConnectionResolverInterface $resolver
	 * @param string   									       $table
	 * @param string  										   $accountTable
	 * @return \Stillat\Database\Tenant\Repositories\DatabaseTenantRepository
	 */
	public function __construct(Resolver $resolver, $table, $accountTable)
	{
		$this->table = $table;
		$this->tenantAccountTable = $accountTable;
		$this->resolver = $resolver;
	}

	/**
	 * Creates the repository. An alias for createRepository()
	 * 
	 * @param  array  $recordDetails - not required
	 * @return mixed
	 */
	public function create(array $recordDetails = array())
	{
		return $this->createRepository();
	}

	/**
	 * Removes the repository. An alias for removeRepository()
	 *
	 * @param  array  $removeDetails - note required
	 * @return mixed
	 */
	public function remove(array $removeDetails = array())
	{
		return $this->removeRepository();
	}


	/**
	 * Updates an existing record.
	 * 
	 * @param  int $recordID
	 * @param  array  $newRecordDetails
	 * @return mixed
	 */
	public function update($recordID, array $newRecordDetails)
	{
		return;
	}

	/**
	 * Creates the tenant repository.
	 * 
	 * @return mixed
	 */
	public function createRepository()
	{
		// Here we are going to retrieve the name of the users table from the
		// authentication configuration that should be set in a default
		// Laravel application installation.

		$application = app();
		$usersTable = $application['config']->get('auth.table', 'users');

		$schema = $this->getConnection()->getSchemaBuilder();

		$schema->create($this->table, function($table)
		{
			$table->increments('id');
			$table->string('tenant_name')->default('')->unique();
			$table->boolean('active')->default(true);
			$table->timestamps();
		});

		$schema->create($this->tenantAccountTable, function($table) use ($usersTable)
		{
			$table->increments('id');
			$table->integer(str_singular($this->table).'_id')->unsigned();
			$table->integer(str_singular($usersTable).'_id')->unsigned();
			$table->index(str_singular($this->table).'_id');
			$table->index(str_singular($usersTable).'_id');
		});
	}

	/**
	 * Removes the tenant repository.
	 * 
	 * @return mixed
	 */
	public function removeRepository()
	{
		$schema = $this->getConnection()->getSchemaBuilder();

		$schema->drop($this->table);
		$schema->drop($this->tenantAccountTable);
	}

	/**
	 * Resolve the database connection instance.
	 *
	 * @return \Illuminate\Database\Connection
	 */
	public function getConnection()
	{
		return $this->resolver->connection($this->connectionName);
	}

	/**
	 * Gets a specific tenant by ID.
	 * 
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function getTenant($tenantID)
	{
		return;
	}

	/**
	 * Creates a log for a given tenant.
	 * 
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function log($tenantID)
	{
		return $this->getTable()->insert(array('tenant_name' => $tenantID, 'active' => true));
	}

	/**
	 * Gets all tenants.
	 * 
	 * @return array
	 */
	public function getTenants()
	{
		$tenants = $this->getTable()->select('id', 'tenant_name', 'active')->get();

		return $tenants;
	}

	/**
	 * Gives a user permission on a tenant.
	 * 
	 * @param  int   $userID
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function grantUserOnTenant($userID, $tenantID)
	{
		return DB::table($this->tenantAccountTable)->insert(array('tenant_id' => $tenantID, 'user_id' => $userID));
	}

	/**
	 * Gets all tenants for a user.
	 * 
	 * @param  int   $userID
	 * @return array
	 */
	public function getUserTenants($userID)
	{
		return DB::getTable($this->tenantAccountTable)->where('user_id', '=', $userID)->get();
	}

	/**
	 * Removes a user from a tenant.
	 * 
	 * @param  int   $userID
	 * @param  int   $tenantID
	 * @return mixed
	 */
	public function removeUserFromTenant($userID, $tenantID)
	{
		return DB::table($this->tenantAccountTable)->where('tenant_id', '=', $tenantID)->where('user_id', '=', $userID)->delete();
	}

}