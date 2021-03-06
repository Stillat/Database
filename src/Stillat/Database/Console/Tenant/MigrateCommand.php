<?php namespace Stillat\Database\Console\Tenant;

use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\TenantManager as Manager;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\Tenant\Migrations\TenantMigrationResolver;
use Illuminate\Database\Console\Migrations\BaseCommand as Command;

class MigrateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:migrate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run migrations on all tenants';

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
	 * Create a new tenant migrations command instance.
	 *
	 * @param  \Stillat\Database\Tenant\Migrations\TenantMigrator  $migrator
	 * @param  string  $packagePath
	 * @return \Stillat\Database\Tenant\Migrations\MigrateCommand
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
		// The pretend option can be used for "simulating" the migration and grabbing
		// the SQL queries that would fire if the migration were to be run against
		// a database for real, which is helpful for double checking migrations.
		$pretend = $this->input->getOption('pretend');

		$path = $this->getMigrationPath();

		$this->migrator->run($path, $pretend);

		foreach ($this->migrator->getNotes() as $note)
		{
			$this->output->writeln($note);
		}

	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('bench', null, InputOption::VALUE_OPTIONAL, 'The name of the workbench to migrate.', null),

			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files.', null),

			array('package', null, InputOption::VALUE_OPTIONAL, 'The package to migrate.', null),

			array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),

			array('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'),
		);
	}

}