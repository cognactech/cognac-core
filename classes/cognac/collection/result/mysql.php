<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */


abstract class Cognac_Collection_Result_MySQL extends Collection_Result
{
	/**
	 * returns instance of Model for current() index of data object
	 *
	 * @return 	Model
	 */
	public function current()
	{
		$data = $this->_data->current();

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
	 * returns primary key for current() data row
	 *
	 * @return 	string
	 * @abstract
	 */
	public function key()
	{
		return $this->_data->key();
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
		return $this->_data->next();
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
		return $this->_data->rewind();
	}

	/**
	 * Checks wether current index is valids
	 *
	 * @return 	bool
	 */
	public function valid()
	{
		return $this->_data->valid();
	}

	/**
	 * returns count of records in data
	 *
	 * @return	int
	 */
	public function count ()
	{
		return $this->_data->count();
	}
}
