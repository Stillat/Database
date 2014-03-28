<?php namespace Stillat\Database\Console\Tenant;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\TenantManager as Manager;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\Tenant\Migrations\TenantMigrationResolver;

class RefreshCommand extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:refresh';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset and re-run all tenant migrations';

	/**
	 * The migrator instance.
	 *
	 * @var \Stillat\Database\Tenant\TenantMigrator
	 */
	protected $migrator;

	/**
	 * The path to the packages directory (vendor).
	 *
	 * @var string
	 */
	protected $packagePath;

	/**
	 * The tenant migration resolver instance.
	 *
	 * @var \Stillat\Database\Tenant\Migrations\TenantMigrationResolver
	 */
	protected $tenantMigrationResolver;

	/**
	 * Create a new tenant migrations refresh instance.
	 *
	 * @param  \Stillat\Database\Tenant\Migrations\TenantMigrator  $migrator
	 * @param  string  $packagePath
	 * @return \Stillat\Database\Tenant\Migrations\RefreshCommand
	 */
	public function __construct(TenantMigrator $migrator, $packagePath)
	{
		parent::__construct();

		$this->migrator = $migrator;
		$this->packagePath = $packagePath;

		// There is nothing special about the TenantMigrationResolver class, so let's just new up one.
		$this->tenantMigrationResolver = new TenantMigrationResolver;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$pretend = $this->input->getOption('pretend');
		$path    = $this->input->getOption('path', null);

		$this->call('tenant:reset', array('--path' => $path, '--pretend' => $pretend));
		$this->call('tenant:migrate', array('--path' => $path, '--pretend' => $pretend));

		$this->laravel['stillat.database.tenant']->restoreLaravel();

		if ($this->needsSeeding())
		{
			$this->runSeeder();
		}

	}

	/**
	 * Determine if the developer has requested database seeding.
	 * 
	 * @return bool
	 */
	protected function needsSeeding()
	{
		return $this->option('seed') || $this->option('seeder');
	}

	protected function runSeeder()
	{
		$class = $this->option('seeder') ?: 'TenantDatabaseSeeder';

		$this->call('tenant:seed', array('--class' => $class));
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),
			array('seed', null, InputOption::VALUE_NONE, 'Indicates if the tenant seed task should be re-run'),
			array('seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'),
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files', null)
		);
	}


}