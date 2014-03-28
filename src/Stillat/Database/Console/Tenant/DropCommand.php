<?php namespace Stillat\Database\Console\Tenant;

use Exception;
use Illuminate\Console\Command;
use Stillat\Database\Tenant\TenantManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DropCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tenant:drop';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Drops an existing tenant with a given name';

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
			$tenantManager->dropTenant($this->argument('name'));
			$this->info($tenantManager->getTierNameWithPrefix($this->argument('name')).' dropped successfully');
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