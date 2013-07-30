<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Collection_Result extends ArrayIterator
{
	/**
	 * DatabaseResult or MongoCursor
	 *
	 * @var		mixed
	 */
	protected $_data;

	/**
	 * @param	Model
	 * @param	mixed		DatabaseResult or MongoCursor
	 * @return 	void
	 */
	public function __construct ($Model, $data)
	{
		if ( ! $Model instanceof Model)
		{
			throw new Kohana_Exception(
				'Invalid Model. Expected instanceof :expected',
				array(':expected' => 'Model')
			);
		}

		$this->_Model = $Model;

		$this->_data = $data;
	}

	/**
	 * Returns the data in the result as an array.
	 * @return array
	 */
	public function as_array()
	{
		return iterator_to_array($this->_data);
	}
}
