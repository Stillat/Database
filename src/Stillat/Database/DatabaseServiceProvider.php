<?php namespace Stillat\Database;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Stillat\Database\Tenant\TenantManager;
use Illuminate\Database\Migrations\Migrator;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\SchemaCreator\SchemaCreatorManager;
use Stillat\Database\Tenant\Repositories\DatabaseTenantRepository;
use Stillat\Database\Console\Tenant\NameCommand as TenantNameCommand;
use Stillat\Database\Console\Tenant\DropCommand as TenantDropCommand;
use Stillat\Database\Console\Tenant\ResetCommand as TenantResetCommand;
use Stillat\Database\Console\Tenant\CreateCommand as TenantCreateCommand;
use Stillat\Database\Console\Tenant\MigrateCommand as TenantMigrateCommand;
use Stillat\Database\Console\Tenant\InstallCommand as TenantInstallCommand;
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
		$this->package('stillat/database', 'stillat');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerSchemaManager();
	}

	public function registerSchemaManager()
	{
		$this->app['stillat.database.tenant.schema'] = $this->app->share(function($app)
		{
			return new SchemaCreatorManager($this->app, $this->app['migration.repository']);
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
