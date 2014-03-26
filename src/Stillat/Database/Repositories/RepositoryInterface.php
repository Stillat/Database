<?php namespace Stillat\Database\Repositories;

interface RepositoryInterface {

	/**
	 * Creates a new record.
	 * 
	 * @param  array  $recordDetails
	 * @return mixed
	 */
	public function create(array $recordDetails);

	/**
	 * Removes an existing record.
	 *
	 * @param  array  $removeDetails
	 * @return mixed
	 */
	public function remove(array $removeDetails);


	/**
	 * Updates an existing record.
	 * 
	 * @param  int $recordID
	 * @param  array  $newRecordDetails
	 * @return mixed
	 */
	public function update($recordID, array $newRecordDetails);

}