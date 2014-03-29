<?php namespace Stillat\Database;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Stillat\Database\Tenant\TenantManager;
use Illuminate\Database\Migrations\Migrator;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\SchemaCreator\SchemaCreatorManager;
use Stillat\Database\Tenant\Repositories\DatabaseTenantRepository;
use Stillat\Database\Console\Tenant\SeedCommand as TenantSeedCommand;
use Stillat\Database\Console\Tenant\NameCommand as TenantNameCommand;
use Stillat\Database\Console\Tenant\DropCommand as TenantDropCommand;
use Stillat\Database\Console\Tenant\ResetCommand as TenantResetCommand;
use Stillat\Database\Console\Tenant\CreateCommand as TenantCreateCommand;
use Stillat\Database\Console\Tenant\MigrateCommand as TenantMigrateCommand;
use Stillat\Database\Console\Tenant\InstallCommand as TenantInstallCommand;
use Stillat\Database\Console\Tenant\RefreshCommand as TenantRefreshCommand;
use Stillat\Database\Console\Tenant\RollbackCommand as TenantRollbackCommand;
use Stillat\Database\Console\Tenant\UninstallCommand as TenantUninstallCommand;
use Stillat\Database\Console\Tenant\MigrationsCommand as TenantMigrationsCommand;

class DatabaseServiceProvider extends ServiceProvider {

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
		$this->package('stillat/database', 'stillat-database');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerSchemaManager();
		$this->registerTenantRepository();
		$this->registerTenantManager();
		$this->registerTenantMigrator();
		$this->registerTenantCommands();
	}

	public function registerSchemaManager()
	{
		$this->app['stillat.database.tenant.schema'] = $this->app->share(function($app)
		{
			return new SchemaCreatorManager($this->app, $this->app['migration.repository']);
		});
	}

	public function registerTenantRepository()
	{
		$this->app->bindShared('stillat.database.tenant.repository', function($app)
		{
			$tenantTables = $app['config']->get('stillat-database::tenants.tableNames', null);

			if ($tenantTables === null)
			{
				$tenantTables = array(
					'tenantTable' => 'tenant',
					'accountsTable' => 'tenant_accounts'
					);
			}

			$tenantTable = $tenantTables['tenantTable'];
			$tenantAccountTable = $tenantTables['accountsTable'];

			return new DatabaseTenantRepository($app['db'], $tenantTable, $tenantAccountTable);
		});
	}

	public function registerTenantManager()
	{
		$this->app['stillat.database.tenant'] = $this->app->share(function($app)
		{
			return new TenantManager($this->app, $this->app['stillat.database.tenant.schema'], $this->app['stillat.database.tenant.repository']);
		});
	}

	public function registerTenantMigrator()
	{
		$this->app['stillat.database.tenant.migrator'] = $this->app->share(function($app)
		{
			$repository = $app['migration.repository'];

			return new TenantMigrator($repository, $app['db'], $app['files'], $app['stillat.database.tenant']);
		});
	}

	/**
	 * Register all of the tenant commands.
	 *
	 * @return void
	 */
	public function registerTenantCommands()
	{
		$commands = array('Install', 'Name', 'Uninstall', 'Migrations', 'Create', 'Drop', 'Migrate', 'Rollback', 'Reset', 'Refresh', 'Seed');

		foreach($commands as $command)
		{
			$this->{'registerTenant'.$command.'Command'}();
		}

		$this->commands(
			'command.tenant.install',
			'command.tenant.name',
			'command.tenant.uninstall',
			'command.tenant.migrations',
			'command.tenant.create',
			'command.tenant.drop',
			'command.tenant.migrate',
			'command.tenant.rollback',
			'command.tenant.reset',
			'command.tenant.refresh',
			'command.tenant.seed'
		);
	}

	protected function registerTenantInstallCommand()
	{
		$this->app->bindShared('command.tenant.install', function($app)
		{
			return new TenantInstallCommand($app['stillat.database.tenant.repository']);
		});
	}

	protected function registerTenantUninstallCommand()
	{
		$this->app->bindShared('command.tenant.uninstall', function($app)
		{
			return new TenantUninstallCommand($app['stillat.database.tenant.repository']);
		});
	}

	protected function registerTenantNameCommand()
	{
		$this->app->bindShared('command.tenant.name', function($app)
		{
			return new TenantNameCommand();
		});
	}

	protected function registerTenantCreateCommand()
	{
		$this->app->bindShared('command.tenant.create', function($app)
		{
			return new TenantCreateCommand();
		});
	}

	protected function registerTenantDropCommand()
	{
		$this->app->bindShared('command.tenant.drop', function($app)
		{
			return new TenantDropCommand();
		});
	}

	public function registerTenantMigrationsCommand()
	{
		$this->app->bindShared('command.tenant.migrations', function($app)
		{
			$packagePath = $app['path.base'].'/vendor';

			return new TenantMigrationsCommand($app['stillat.database.tenant'], $app['migrator'], $packagePath);
		});
	}

	public function registerTenantResetCommand()
	{
		$this->app->bindShared('command.tenant.reset', function($app)
		{
			return new TenantResetCommand($app['stillat.database.tenant.migrator']);
		});
	}

	public function registerTenantMigrateCommand()
	{
		$this->app->bindShared('command.tenant.migrate', function($app)
		{
			$packagePath = $app['path.base'].'/vendor';

			return new TenantMigrateCommand($app['stillat.database.tenant.migrator'], $packagePath);
		});
	}

	public function registerTenantRollbackCommand()
	{
		$this->app->bindShared('command.tenant.rollback', function($app)
		{
			$packagePath = $app['path.base'].'/vendor';

			return new TenantRollbackCommand($app['stillat.database.tenant.migrator'], $packagePath);
		});
	}

	public function registerTenantRefreshCommand()
	{
		$this->app->bindShared('command.tenant.refresh', function($app)
		{
			$packagePath = $app['path.base'].'/vendor';

			return new TenantRefreshCommand($app['stillat.database.tenant.migrator'], $packagePath);
		});
	}

	public function registerTenantSeedCommand()
	{
		$this->app->bindShared('command.tenant.seed', function($app)
		{
			return new TenantSeedCommand($app['db'], $app['stillat.database.tenant']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
