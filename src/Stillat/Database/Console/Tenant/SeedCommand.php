<?php namespace Stillat\Database\Console\Tenant;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Stillat\Database\Tenant\TenantManager as Manager;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class SeedCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seed the tenant databases with records';

	/**
	 * The connection resolver instance.
	 *
	 * @var  \Illuminate\Database\ConnectionResolverInterface
	 */
	protected $resolver;

	/**
	 * The tenant manager.
	 * 
	 * @var \Stillat\Database\Tenant\TenantManager
	 */
	protected $manager;

	/**
	 * Create a new database seed command instance.
	 *
	 * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
	 * @param  \Stillat\Database\Tenant\TenantManager $manager
	 * @return void
	 */
	public function __construct(Resolver $resolver, Manager $manager)
	{
		parent::__construct();

		$this->resolver = $resolver;

		$this->manager = $manager;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$tenants = $this->manager->getRepository()->getTenants();

		$seeder = $this->getSeeder();

		foreach ($tenants as $tenant)
		{
			$this->manager->bootstrapConnectionByTenantName($tenant->tenant_name);
			$this->resolver->setDefaultConnection($tenant->tenant_name);
			$this->manager->assumeTenant($tenant->id, true);
			$seeder->run();
			$this->output->writeln('<info>Seeded tenant '.$tenant->tenant_name.'</info>');
			$this->manager->restoreTenant();
		}

	}

	/**
	 * Get a seeder instance from the container.
	 * 
	 * @return \Illuminate\Database\Seeder
	 */
	protected function getSeeder()
	{
		$class = $this->laravel->make($this->input->getOption('class'));

		return $class->setContainer($this->laravel)->setCommand($this);
	}

	/**
	 * Get the console command options.
	 * 
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'TenantDatabaseSeeder'),
		);
	}


}