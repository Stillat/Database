<?php namespace Stillat\Database\Console\Tenant;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates the new tenant with a given name';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$tenantManager = $this->laravel['stillat.database.tenant'];
		
		try
		{
			$tenantManager->createTenant($this->argument('name'));
			$this->info($tenantManager->getTierNameWithPrefix($this->argument('name')).' created successfully');
		} catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The account ID in question')
		);
	}

}