<?php namespace Stillat\Database\Repositories;

use Stillat\Database\Repositories\RepositoryInterface

abstract class BaseRepository implements RepositoryInterface {

	/**
	 * Creates a new record.
	 * 
	 * @param  array  $recordDetails
	 * @return mixed
	 */
	abstract public function create(array $recordDetails);

	/**
	 * Removes an existing record.
	 *
	 * @param  array  $removeDetails
	 * @return mixed
	 */
	abstract public function remove(array $removeDetails);


	/**
	 * Updates an existing record.
	 * 
	 * @param  int $recordID
	 * @param  array  $newRecordDetails
	 * @return mixed
	 */
	abstract public function update($recordID, array $newRecordDetails);

}