<?php namespace Stillat\Database\SchemaCreator;

use Illuminate\Foundation\Application;
use Stillat\Database\Tenant\TenantManager;
use Stillat\Common\Exceptions\InvalidArgumentException;
use Stillat\Database\SchemaCreator\Drivers\MySqlDriver;
use Stillat\Database\SchemaCreator\Drivers\PostgresDriver;
use Stillat\Database\SchemaCreator\Drivers\SqlServerDriver;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class SchemaCreatorManager {

	/**
	 * The application instnace.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * The SchemaCreatorInterface implementation.
	 *
	 * @var \Stillat\Database\SchemaCreator\SchemaCreatorInterface
	 */
	protected $schemaCreatorDriver;

	/**
	 * The schema creator implementation.
	 *
	 * @var \Stillat\Database\SchemaCreator\SchemaCreatorInterface
	 */
	protected $schemaDriver;

	/**
	 * The repository instance.
	 *
	 * @var \Illuminate\Database\Migrations\MigrationRepositoryInterface
	 */
	protected $repository;

	/**
	 * Returns a new SchemaCreatorManager instance.
	 * 
	 * @param Application                  $app
	 * @param MigrationRepositoryInterface $repository
	 */
	public function __construct(Application $app, MigrationRepositoryInterface $repository)
	{
		$this->app = $app;

		$this->repository = $repository;

		// Here we are going to resolve the schema driver. We will do this by getting the driver
		// for the default database connection. As far as the tenant service is concerned, the default
		// database connection will act as the hub.
		$dataConnections = $this->app['config']->get(TenantManager::CONFIGURATION_KEY_NAME_PREFIX);
		$defaultConnection = $dataConnections[$this->app['config']->get(TenantManager::CONFIGURATION_DEFAULT_CONNECTION_NAME)];

		$defaultConnectionDriver = $defaultConnection['driver'];

		$hostName = $defaultConnection['host'];
		$username = $defaultConnection['username'];
		$password = $defaultConnection['password'];

		switch ($defaultConnectionDriver)
		{
			case 'mysql':
				$this->schemaDriver = new MySqlDriver($hostName, $username, $password);
				break;
			case 'pgsql':
				$this->schemaDriver = new PostgresDriver($hostName, $username, $password);
				break;
			case 'sqlsrv':
				$this->schemaDriver = new SqlServerDriver($hostName, $username, $password);
				break;
			default:
				throw new InvalidArgumentException("Driver '{$defaultConnectionDriver}' is not a valid schema creator driver.");
				break;
		}
	}

	/**
	 * Returns the schema driver being used.
	 * 
	 * @return \Stillat\Database\SchemaCreator\SchemaCreatorInterface
	 */
	public function getDriver()
	{
		return $this->schemaDriver;
	}

	/**
	 * Returns the migration repository.
	 * 
	 * @return \Illuminate\Database\Migrations\MigrationRepositoryInterface
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	/**
	 * Creates a new database schema.
	 * 
	 * @param  string $schemaName
	 * @return bool
	 */
	public function createSchema($schemaName)
	{
		$schema = $this->schemaDriver->createSchema($schemaName);
		$this->repository->setSource($schemaName);
		$this->repository->createRepository();
		return $schema;
	}

	/**
	 * Drops an existing database schema.
	 * 
	 * @param  string $schemaName
	 * @return bool
	 */
	public function dropSchema($schemaName)
	{
		return $this->schemaDriver->dropSchema($schemaName);
	}

}