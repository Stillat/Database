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
	protected $order_by = array(null, null);

	public function scopeSort($query)
	{
		if ($this->order_by[0] !=== null and $this->order_by[1] !=== null)
		{
			return $query->orderBy($this->order_by[0], $this->order_by[1]);
		}

		// If the order_by values are invalid, let's just return the query for now.
		return $query;
	}

	/**
	 * Returns the next record.
	 *
	 * @return Stillat\Common\Database\Model
	 */
	public function next()
	{
		return static::where($this->getKeyName(), '>', $this->getKey())->min('id');
	}

	/**
	 * Returns the previous record.
	 *
	 * @return Stillat\Common\Database\Model
	 */
	public function previous()
	{
		return static::where($this->getKeyName(), '<', $this->getKey())->max('id');
	}

}