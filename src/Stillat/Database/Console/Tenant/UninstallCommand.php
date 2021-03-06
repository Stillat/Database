<?php namespace Stillat\Database\Console\Tenant;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\Repositories\DatabaseTenantRepository;

class UninstallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:uninstall';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Removes the tenant repository';

	/**
	 * The repository instance.
	 *
	 * @var \Stillat\Database\Tenant\DatabaseTenantRepository
	 */
	protected $repository;

	/**
	 * Create a new tenant uninstall command instance.
	 *
	 * @param  \Stillat\Database\Tenant\DatabaseTenantRepository  $repository
	 * @return void
	 */
	public function __construct(DatabaseTenantRepository $repository)
	{
		parent::__construct();

		$this->repository = $repository;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->repository->setConnection($this->input->getOption('database'));

		$this->repository->removeRepository();

		$this->info("Tenant table removed successfully.");
		$this->info("Tenant account table removed successfully.");
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
		);
	}

}