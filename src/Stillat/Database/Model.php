<?php namespace Stillat\Database;

use LaravelBook\Ardent\Ardent;

abstract class Model extends Ardent {

	/**
	 * The order by column name and order. The array is setup such
	 * that the first element is the column name and the second column
	 * is the sorting direction (i.e., ASC or DESC).
	 * 
	 * @var array
	 */
	protected $orderBy = array(null, null);

	/**
	 * Determines if the model will automatically order query results.
	 * 
	 * @var boolean
	 */
	protected $automaticallyOrder = false;

	/**
	 * A list of method names that are ignored when determining if the model
	 * should automatically ordery the query.
	 * 
	 * @var array
	 */
	protected $ignoredOrderMethods = array('increment', 'decrement');

	/**
	 * Orders a given query.
	 * 
	 * @param  \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOrder($query)
	{
		if ($this->orderBy[0] !== null and $this->orderBy[1] !== null)
		{
			return $query->orderBy($this->orderBy[0], $this->orderBy[1]);
		}

		// If the orderBy values are invalid, let's just return the query for now.
		return $query;
	}

	/**
	 * Handle dynamic method calls into the method.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		// Handle the automatic ordering of query results.
		if ($this->automaticallyOrder and !in_array($method, $this->ignoredOrderMethods))
		{
			return $this->scopeOrder($this->newQuery());
		}

		// Handle the default Eloquent model __call
		return parent::__call($method, $parameters);
	}

	/**
	 * Returns the next record.
	 *
	 * @return Stillat\Database\Model
	 */
	public function next()
	{
		return static::where($this->getKeyName(), '>', $this->getKey())->min($this->getKeyName());
	}

	/**
	 * Returns the previous record.
	 *
	 * @return Stillat\Database\Model
	 */
	public function previous()
	{
		return static::where($this->getKeyName(), '<', $this->getKey())->max($this->getKeyName());
	}

}