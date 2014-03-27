<?php namespace Stillat\Database\Console\Tenant;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\Migrations\TenantMigrator;
use Stillat\Database\Tenant\TenantManager as Manager;
use Stillat\Database\Tenant\Migrations\TenantMigrationResolver;

class ResetCommand extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollback all database migrations for each tenant';

	/**
	 * The migrator instance.
	 *
	 * @var \Stillat\Common\Database\Tenant\Migrations\TenantMigrator
	 */
	protected $migrator;

	/**
	 * Create a new tenant migration rollback command instance.
	 *
	 * @param  \Stillat\Common\Database\Tenant\Migrations\TenantMigrator  $migrator
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

		$path    = $this->input->getOption('path', null);

		if ($path !== null)
		{
			$this->migrator->usePath($path);
		}

		while (true)
		{
			$count = $this->migrator->rollback($pretend);

			foreach ($this->migrator->getNotes() as $note)
			{
				$this->output->writeln($note);
			}

			if ($count == 0)
			{
				break;
			}
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
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files', null)
			);
	}

}