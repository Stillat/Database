<?php namespace Stillat\Database\Console\Tenant;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\Repositories\DatabaseTenantRepository;

class InstallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create the tenant repository';

	/**
	 * The repository instance.
	 *
	 * @var \Stillat\Database\Tenant\Repositories\DatabaseTenantRepository
	 */
	protected $repository;

	/**
	 * Create a new tenant install command instance.
	 *
	 * @param  \Stillat\Database\Tenant\Repositories\DatabaseTenantRepository  $repository
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

		$this->repository->createRepository();

		$this->info("Tenant table created successfully.");
		$this->info("Tenant account table created successfully.");
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