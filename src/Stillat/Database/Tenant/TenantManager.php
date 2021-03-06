<?php namespace Stillat\Database\Tenant;

use Illuminate\Foundation\Application;
use Stillat\Common\Exceptions\InvalidArgumentException;
use Stillat\Database\SchemaCreator\SchemaCreatorManager;
use Stillat\Database\Tenant\Repositories\TenantRepositoryInterface;

class TenantManager {

	/**
	 * The name of the tenancy session directive.
	 *
	 * @var string
	 */
	const TENANT_SESSION_DIRECTIVE_NAME = 'tenancy.connectionName';

	/**
	 * The separator to use for separating schema names from prefixes.
	 *
	 * @var string
	 */
	const TENANT_SCHEMA_SEPARATOR = '_';

	/**
	 * The name of the Laravel configuration directive for database connections.
	 *
	 * @var string
	 */
	const CONFIGURATION_KEY_NAME_PREFIX = 'database.connections';

	/**
	 * The name of the Laravel configuration directive for default database connections.
	 *
	 * @var string
	 */
	const CONFIGURATION_DEFAULT_CONNECTION_NAME = 'database.default';

	/**
	 * The fully qualified name of the Laravel migration base class.
	 *
	 * @var string
	 */
	const LARAVEL_MIGRATION_BASE_CLASS = 'Illuminate\Database\Migrations\Migration';

	/**
	 * The application instnace.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * The migrations used by the tenants.
	 *
	 * @var array
	 */
	protected $tenantMigrations = array();

	/**
	 * The schema prefix that should be used, if any.
	 *
	 * @var string
	 */
	protected $schemaPrefix = '';

    /**
     * The database prefix, if any.
     *
     * @var string
     */
    protected $databasePrefix = '';

	/**
	 * Indicates whether the manager will preserve database configuration
	 * settings for read/write servers.
	 *
	 * @var bool
	 */
	protected $preserveReadWriteConfiguration = false;

	/**
	 * The collection of connections the tenant manager is hosting.
	 *
	 * @var array
	 */
	protected $tenantConnections = array();

	/**
	 * The SchemaCreatorManager instance.
	 *
	 * @var \Stillat\Database\SchemaCreator\SchemaCreatorManager
	 */
	protected $schemaManager = null;

	/**
	 * The TenantRepository implementation.
	 *
	 * @var \Stillat\Database\Tenant\Repositories\TenantRepositoryInterface
	 */
	protected $tenantRepository = null;

	/**
	 * The behavior of the migration behavior.
	 *
	 * @var string
	 */
	protected $migrationBehavior = 'exclude';

	/**
	 * The accepted accepted migration behaviors.
	 *
	 * @var array
	 */
	public static $acceptedMigrationBehaviors = array('only', 'exclude');

	/**
	 * The default connection as determined by Laravel.
	 * 
	 * @var string
	 */
	protected $laravelDefaultConnection = '';

	/**
	 * The connection to restore, if any.
	 * 
	 * @var string
	 */
	protected $restorableConnection = '';

	/**
	 * Returns a new instance of the tenant manager.
	 * 
	 * @param Application               $app
	 * @param SchemaCreatorManager      $manager
	 * @param TenantRepositoryInterface $repository
	 */
	public function __construct(Application $app, SchemaCreatorManager $manager, TenantRepositoryInterface $repository)
	{
		$this->app = $app;

		$this->schemaManager = $manager;

		$this->tenantRepository = $repository;

		$this->schemaPrefix = $this->app['config']->get('stillat-database::tenants.schemaPrefix', '');

        $this->databasePrefix = $this->app['config']->get('stillat-database::tenants.databasePrefix', '');

		$this->preserveReadWriteConfiguration = $this->app['config']->get('stillat-database::tenants.preserveReadWrite', false);

		// Get the migration behavior from the tenant configuration file.
		$this->migrationBehavior = $this->app['config']->get('stillat-database::tenants.migrationBehavior', 'exclude');

		// Just convert the migration behavior to lower-case.
		$this->migrationBehavior = strtolower($this->migrationBehavior);

		if (in_array($this->migrationBehavior, self::$acceptedMigrationBehaviors) == false)
		{
			// If the provided migration behavior is not in the accepted behavior list, we are just going
			// to reset it to 'exclude'.
			$this->migrationBehavior = 'exclude';
		}

		// Check to see if there are any migrations listed in the tenant configuration file. If there are,
		// try and add them.
		$tenantMigrationCollection = $this->app['config']->get('stillat-database::tenants.migrations', null);

		if ($tenantMigrationCollection !== null and is_array($tenantMigrationCollection) and count($tenantMigrationCollection) > 0)
		{
			foreach ($tenantMigrationCollection as $migration)
			{
				$this->addMigration($migration);
			}
		}


		// Set the default Laravel connection name.
		$this->laravelDefaultConnection = $this->app['config']->get('database.default');

	}

	/**
	 * Bootstraps a connection.
	 * 
	 * @param  string $tenantName
	 * @return string
	 */
	public function bootstrapConnectionByTenantName($tenantName)
	{
		if (in_array($tenantName, $this->tenantConnections) == false)
		{
			$connectionKey = self::CONFIGURATION_KEY_NAME_PREFIX.'.'.$tenantName;

			$configuration = $this->app['config'];

			$dataConnections = $configuration->get(self::CONFIGURATION_KEY_NAME_PREFIX);
			$defaultConnection = $dataConnections[$configuration->get(self::CONFIGURATION_DEFAULT_CONNECTION_NAME)];

			$tenantConnectionSettings = array();

			if ($this->preserveReadWriteConfiguration)
			{
				// If the user knows what they are doing and really wants to preserve the
				// read/write server settings, just let them.
				$tenantConnectionSettings = $defaultConnection;
			}
			else
			{
				// Remove the read and write configuration settings if they exist. It doesn't make sense
				// to use the default connections read/write settings if the user will be connecting to
				// their own database and connection.
				$tenantConnectionSettings = array_except($defaultConnection, array('read', 'write'));
			}

			// This simply overrides the database name.
			$tenantConnectionSettings['database'] = $tenantName;

			// This will build the new database connection for the request.
			$this->app['config']->set($connectionKey, $tenantConnectionSettings);

			$this->tenantConnections[] = $tenantName;
		}

		return $tenantName;
	}

	/**
	 * Causes the tenant manager to register a new database connection for
	 * a given account ID.
	 *
	 * @param  int  $accountID
	 * @return string
	 */
	protected function bootstrapConnection($accountID)
	{
		return $this->bootstrapConnectionByTenantName($this->getTierNameWithPrefix($accountID));
	}

	/**
	 * Causes the tenant manager to switch the current connection
	 * to the account with the given account ID.
	 *
	 * @param int $accountID
	 * @param bool $restorable
	 */
	public function assumeTenant($accountID, $restorable = false)
	{
		if ($restorable)
		{
			$this->restorableConnection = $this->getCurrentConnection();
		}

		$this->app['session']->put(self::TENANT_SESSION_DIRECTIVE_NAME, $this->bootstrapConnection($accountID));
	}

	/**
	 * Restores the previous tenant connection.
	 * 
	 * @return void
	 */
	public function restoreTenant()
	{
		$this->app['session']->put(self::TENANT_SESSION_DIRECTIVE_NAME, $this->restorableConnection);
	}

	/**
	 * Restores the default Laravel connection.
	 * 
	 * @return void
	 */
	public function restoreLaravel()
	{
		$this->app['config']->set('database.default', $this->laravelDefaultConnection);
	}

	/**
	 * Returns the current connection name.
	 *
	 * @return mixed
	 */
	public function getCurrentConnection()
	{
		$accountName = $this->app['session']->get(self::TENANT_SESSION_DIRECTIVE_NAME, null);

		if ($accountName == null)
		{
			return null;
		}
		else
		{
			return $accountName;
		}
	}

	/**
	 * Returns the name of a tenant tier.
	 *
	 * @param  int $tierID
	 * @return string
	 */
	public function getTierName($tierID)
	{
		$tierID = intval($tierID);

		// All this does is perform some string replacement functions
		// to convert a numeric-based $tierID to a string representation.
		// This is all arbitrary, it just has to be consistent.

		$tierName = md5($tierID);
		$tierName = str_replace('1', 'a', $tierName);
		$tierName = str_replace('2', 'c', $tierName);
		$tierName = str_replace('3', 'n', $tierName);
		$tierName = str_replace('4', 'q', $tierName);
		$tierName = str_replace('5', 'i', $tierName);
		$tierName = str_replace('6', 'm', $tierName);
		$tierName = str_replace('7', 'k', $tierName);
		$tierName = str_replace('8', 'e', $tierName);
		$tierName = str_replace('9', 'o', $tierName);
		$tierName = str_replace('0', 'z', $tierName);
		$tierName = strtolower($tierName);

		return $tierName;
	}

	/**
	 * Returns the name of a tenant tier withs its schema prefix, if any.
	 *
	 * @param  int $tierID
	 * @return string
	 */
	public function getTierNameWithPrefix($tierID)
	{
		if (strlen($this->getSchemaPrefix() == 0))
		{
			return $this->databasePrefix.$this->getTierName($tierID);
		}

		return $this->getSchemaPrefix().self::TENANT_SCHEMA_SEPARATOR.$this->databasePrefix.$this->getTierName($tierID);
	}

	/**
	 * Returns the configured schema prefix.
	 *
	 * @return string
	 */
	public function getSchemaPrefix()
	{
		return $this->schemaPrefix;
	}

	/**
	 * Adds a migration to the manager's migration list.
	 *
	 * @param string $migrationName
	 * @throws InvalidArgumentException
	 */
	public function addMigration($migrationName)
	{
		if (class_exists($migrationName))
		{

			//$this->tenantMigrations[] = $migrationName;

			if (is_subclass_of($migrationName, self::LARAVEL_MIGRATION_BASE_CLASS))
			{
				$this->tenantMigrations[] = $migrationName;
			}
			else
			{
				throw new InvalidArgumentException("The class '{$migrationName}' does not extend '".self::LARAVEL_MIGRATION_BASE_CLASS."'");
			}
		}
		else
		{
			throw new InvalidArgumentException("The migration '{$migrationName}' does not exist, or cannot be found.");
		}		
	}

	/**
	 * Removes a migration from the manager's migration list.
	 *
	 * @param string $migrationName
	 */
	public function removeMigration($migrationName)
	{
		unset($this->tenantMigrations[$migrationName]);
	}

	/**
	 * Returns the migrations associated with the tenant system.
	 *
	 * @return array
	 */
	public function getTenantMigrations()
	{
		return $this->tenantMigrations;
	}

	/**
	 * Returns the migration behavior.
	 *
	 * @return string
	 */
	public function getMigrationBehavior()
	{
		return $this->migrationBehavior;
	}

	/**
	 * Returns the internal SchemaCreatorManager instance.
	 *
	 * @return \Stillat\Database\SchemaCreator\SchemaCreatorManager
	 */
	public function getSchemaManager()
	{
		return $this->schemaManager;
	}

	public function getRepository()
	{
		return $this->tenantRepository;
	}

	public function createTenant($tenantID)
	{
		$this->bootstrapConnectionByTenantName($this->getTierNameWithPrefix($tenantID));
		$this->schemaManager->createSchema($this->getTierNameWithPrefix($tenantID));
		$this->tenantRepository->log($this->getTierNameWithPrefix($tenantID));

		// The next thing we have to do is install a migrations table for the new tenant.
	}

	public function dropTenant($tenantID)
	{
		$this->schemaManager->dropSchema($this->getTierNameWithPrefix($tenantID));
		$this->tenantRepository->removeTenant($this->getTierNameWithPrefix($tenantID));
	}

	/**
	 * Returns an instance of the Tenant Manager
	 *
	 * @return \Stillat\Database\Tenant\TenantManager
	 */
	public static function instance()
	{
		$application = app();
		return $application->make('stillat.database.tenant');
	}

}