<?php namespace Stillat\Database\Repositories;

use Illuminate\Support\Facades\DB;
use Stillat\Database\Repositories\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface {

	/**
	 * The name of the database connection to use.
	 * 
	 * @var string
	 */
	protected $connectionName = '';

	/**
	 * The name of the database table this repository represents.
	 * 
	 * @var string
	 */
	protected $table = '';

	/**
	 * Creates a new record.
	 * 
	 * @param  array  $recordDetails
	 * @return mixed
	 */
	abstract public function create(array $recordDetails = array());

	/**
	 * Removes an existing record.
	 *
	 * @param  array  $removeDetails
	 * @return mixed
	 */
	abstract public function remove(array $removeDetails = array());


	/**
	 * Updates an existing record.
	 * 
	 * @param  int $recordID
	 * @param  array  $newRecordDetails
	 * @return mixed
	 */
	abstract public function update($recordID, array $newRecordDetails);

	/**
	 * Sets the name of the connection to use.
	 * 
	 * @param string $connectionName
	 */
	public function setConnection($connectionName)
	{
		$this->connectionName = $connectionName;
	}

	/**
	 * Gets the name of the connection used by the repository.
	 * 
	 * @return string
	 */
	public function getConnection()
	{
		return $this->connectionName;
	}

	/**
	 * Returns a query builder instance for the table.
	 * 
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function getTable()
	{
		return DB::connection($this->connectionName)->table($this->table);
	}

}