<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Collection_MySQL extends Collection
{
	/**
	  * searches mysql table and returns Collection_Result
	  *
	  * [!] An empty $fields array will return all keys for document
	  *
	  * @param	array 		$where		// key => value pairs to filter on
	  * @param	array 		$fields		// keys to return
	  * @param	int			$start		// record to start searching at
	  * @param	int			$limit		// how many records to return
	  * @param	array 		$sorting	// array of key = fields and value = sort order
	  * @return	bool					// if no results are found
	  * @return	Collection_Result		// if results are found
	  */
	public function find_all (Array $where=array(), Array $fields=array(), $start=0, $limit=null, Array $sorting=array())
	{
		// query mysql using query builder with info passed
		if ( count($fields) > 0 )
		{
			$query = DB::select($fields)
					->from($this->_tableName);
		}
		else
		{
			$query = DB::select()
					->from($this->_tableName);
		}
		
		foreach ($where AS $key => $value)
		{
			if ( ! is_array($where[$key]) )
			{
				$query->where($key, '=', $value);
			}
			else {
				$query->where($key, $value[0], $value[1]);
			}
		}

		// add offset / starting point
		if ( (int) $start > 0 ) $query->offset( (int) $start);

		// add limit if it is passed
		if ( ! is_null($limit) AND (int) $limit > 0 ) {
			$query->limit( (int) $limit);
		}

		// add order by for each option in $sorting array
		if (is_array($sorting) AND count($sorting) > 0) {
			foreach ($sorting AS $sort_field => $value)
			{
				$sort_order = ( (int) $value > 0) ? 'ASC' : 'DESC' ;
				$query->order_by($sort_field, $sort_order);
			}
		}

		$results = $query->execute($this->_db);

		// no results return false
		if ($results->count() <= 0) return false;

		$Result = new Collection_Result_MySQL($this->_Model, $results);

		return $Result;
	}

	/**
	  * searches mysql table and returns count of matching records
	  *
	  * @param	array 		$where		// key => value pairs to filter on
	  * @return	bool
	  */
	public function count_all (Array $where=array())
	{
		// query mysql using query builder with info passed
		$query = DB::select()
				->from($this->_tableName);
		
		foreach ($where AS $key => $value)
		{
			if ( ! is_array($value))
				$query->where($key, '=', $value);
			else
				$query->where($key, $value[0], $value[1]);
		}

		$result = $query->execute($this->_db);

		return $result->count();
	}
}