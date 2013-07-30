<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Collection_Result_Mongo extends Collection_Result
{
	protected $_iteration = 0;

	protected $_getNext = TRUE;

	protected $_DEBUG = 50;

	/**
	 * returns instance of Model for current() index of data object
	 *
	 * @return 	Model
	 */
	public function current()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		$data = $this->_data->current();

		if ( ! $data AND $this->_iteration == 0)
			$data = $this->_data->getNext();

		if ($data) {
			//recreate the model
			$model_name = get_class($this->_Model);
			$model = new $model_name;

			$model->load_array($data);
			return $model;
		}

		return false;
	}

	/**
	 * returns _id key for current() data row
	 *
	 * @return 	string
	 * @abstract
	 */
	public function key()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		// return _id key for current row
		$row = $this->_data->current();
		
		return (string) $row['_id'];
	}

	/**
	 * Skips to next record in _data
	 *
	 * [!] current() will reflect Model for new row
	 *
	 * @return 	void
	 */
	public function next()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		$this->_data->next();

		$this->_iteration++;
	}

	/**
	 * Starts array pointer at first record in _data
	 *
	 * [!] current() will reflect Model for first row
	 *
	 * @return 	void
	 */
	public function rewind()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		$this->_iteration = 0;
		
		$this->_data->rewind();
	}

	/**
	 * Checks wether current index is valid
	 *
	 * @return 	bool
	 */
	public function valid()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;
		
		return ( $this->_iteration < $this->_data->count(TRUE) )
			? TRUE : FALSE ;
	}

	/**
	 * returns count of records found for cursor
	 *
	 * @return	int
	 */
	public function count ()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		return $this->_data->count(TRUE);
	}

	/**
	 * returns count of all records in cursor
	 *
	 * [!] This method will ignore $limit and $skip
	 *
	 * @return	int
	 */
	public function count_all ()
	{
		if ($this->_DEBUG <= KOHANA::$environment)
			echo __CLASS__ . '::' . __METHOD__ . PHP_EOL;

		return $this->_data->count(FALSE);
	}
}
