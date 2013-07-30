<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Collection
{
	/**
	 * @param	array
	 * @param	array
	 * @param	int
	 * @param	int
	 * @return 	Collection_Result
	 * @abstract
	 */
	abstract public function find_all (Array $where=array(), Array $fields=array(), $start=0, $limit=null);

	/**
	 * @param	array
	 * @return 	int
	 * @abstract
	 */
	abstract public function count_all (Array $where=array());

	/**
	 * @var  array  Collection instances
	 */
	public static $instances = array();

	/**
	 * Get a singleton Collection instance. If configuration is not specified,
	 * it will be loaded from the Collection configuration file using the same
	 * group as the name.
	 *
	 *     // Load the default Collection db
	 *     $data = Collection::instance();
	 *
	 *     // Create a custom configured instance
	 *     $data = Collection::instance('custom', $config);
	 *
	 * @param	string		instance name
	 * @param	array 		configuration parameters
	 * @return 	Collection
	 */
	public static function instance($model)
	{
		$name = $model;

		if ( ! isset(Collection::$instances[$name]))
		{
			$ModelObj = Model::factory($model);
			
			$collection = 'Collection_' . ucwords($model);

			if ( ! class_exists($collection)) {
				$collection = 'Collection_' . $ModelObj->subType;
			}

			// Create the Collection db connection instance
			new $collection($name, $ModelObj);
		}

		return Collection::$instances[$name];
	}

	/**
	 * Holds instance of Model for collection
	 *
	 * @var  Model
	 */
	protected $_Model;

	/**
	 * Holds instance of Model for collection
	 *
	 * @var  string
	 */
	protected $_db;

	/**
	 * Holds instance of Model for collection
	 *
	 * @var  string
	 */
	protected $_dbName;

	/**
	 * Stores the name of instance
	 *
	 * [!!] This method cannot be accessed directly, you must use [Collection::instance].
	 *
	 * @return  void
	 */
	protected function __construct($name, $Model)
	{
		// Set the instance name
		$this->_instance = $name;

		$this->_Model = $Model;

		list($this->_db, $this->_dbName, $this->_tableName) = $this->_Model->db();

		// Store the collection instance
		Collection::$instances[$name] = $this;
	}

	protected function get_object_table_name()
	{
		if($this->_Model instanceof Model)
		{
			
		}
	}
}
