<?php namespace Stillat\Database\Console\Tenant;

use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\TenantManager as Manager;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\Tenant\Migrations\TenantMigrationResolver;
use Illuminate\Database\Console\Migrations\BaseCommand as Command;

class RollbackCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:rollback';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollback the last database migration for all tenants';

	/**
	 * The migrator instance.
	 *
	 * @var \Stillat\Database\Tenant\TenantMigrator
	 */
	protected $migrator;

	/**
	 * Create a new migration rollback command instance.
	 *
	 * @param  \Illuminate\Database\Migrations\Migrator  $migrator
	 * @return void
	 */
	public function __construct(TenantMigrator $migrator)
	{
		parent::__construct();

		$this->migrator = $migrator;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$pretend = $this->input->getOption('pretend');
		$path	 = $this->input->getOption('path', null);

		if ($path !== null)
		{
			$this->migrator->usePath($path);
		}

		$this->migrator->rollback($pretend);

		// Once the migrator has run we will grab the note output and send it out to
		// the console screen, since the migrator itself functions without having
		// any instances of the OutputInterface contract passed into the class.
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
			array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files.', null),
		);
	}

}