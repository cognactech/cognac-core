<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Collection_Mongo extends Collection
{
	/**
	  * searches mongo collection and returns Collection_Result
	  *
	  * [!] An empty $fields array will return all keys for document
	  *
	  * @param	array 		$where		// key => value pairs to filter on
	  * @param	array 		$fields		// keys to return
	  * @param	int		$start		// record to start searching at
	  * @param	int		$limit		// how many records to return
	  * @param	array 		$sorting	// array of key = fields and value = sort order
	  * @return	bool				// if no results are found
	  * @return	Collection_Result		// if results are found
	  */
	public function find_all (Array $where=array(), Array $fields=array(), $start=0, $limit=null, Array $sorting=array())
	{
		if ($limit !== NULL) {
			$where['$limit'] = $limit;
			$where['$offset'] = $start;
		}

		// use db Collection instance to query mongo
		$result = $this->_db->get_items($this->_tableName, $where, $fields);

		// no results return false
		if ($result->count(TRUE) <= 0) return false;

		if (is_array($sorting) AND count($sorting) > 0) {
			$result->sort($sorting);
		}

		// instantiate result iterator passing it the data
		$Result = new Collection_Result_Mongo($this->_Model, $result);

		// return iterator
		return $Result;
	}

	/**
	  * searches mongo collection and returns array of results
	  *
	  * [!] An empty $fields array will return all keys for document
	  *
	  * @param	array 		$where		// key => value pairs to filter on
	  * @param	array 		$fields		// keys to return
	  * @param	int		$start		// record to start searching at
	  * @param	int		$limit		// how many records to return
	  * @param	array 		$sorting	// array of key = fields and value = sort order
	  * @return	bool				// if no results are found
	  * @return	array 				// if results are found
	  */
	public function get_all (Array $where=array(), Array $fields=array(), $start=0, $limit=null, Array $sorting=array())
	{
		if ($limit !== NULL) {
			$where['$limit'] = $limit;
			$where['$offset'] = $start;
		}

		// use db Collection instance to query mongo
		$result = $this->_db->get_items($this->_tableName, $where, $fields);

		// no results return false
		if ($result->count(TRUE) <= 0) return false;

		if (is_array($sorting) AND count($sorting) > 0) {
			$result->sort($sorting);
		}

		return iterator_to_array($result);
	}

	/**
	  * searches mongo collection and returns count of matching documents
	  *
	  * @param	array 		$where		// key => value pairs to filter on
	  * @return	bool
	  */
	public function count_all (Array $where=array())
	{
		// use db Collection instance to query mongo
		$result = $this->_db->get_items($this->_tableName, $where);

		return (int) $result->count(TRUE);
	}
}
