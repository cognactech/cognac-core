<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Base model class. It's extended by sql/nosql models.
 *
 * @package
 * @author	Cesar Gonzalez	<c.o.gonz@cognactechnologies.com>
 */

abstract class Model
{
	/**
	 * @var Holds the columns of the model
	 */
	protected $_columns = array();

	/**
	 * @var Holds the columns of the that should be encrypted
	 */
	protected $_columns_crypt = array();

	/**
	 * @var Name of the model
	 */

	/**
	 * @var Holds the data for the model
	 */
	protected $_data = array();

	/**
	 * @var Holds changed data
	 */
	protected $_data_changed = array();

	/**
	 * @var The table name used to store the data
	 */
	protected $_tableName;

	/**
	 * @var Holds the primary key of the model
	 */
	protected $_primaryKey;

	/**
	 * @var The type of model. Valid types are sql and nosql
	 */
	public $type;

	/**
	 * @var The subtype of the mode. MySQL for SQL, Mongo, Dynamo, etc for noSQL
	 */
	public $subType;

	/**
	 * @var Debug level.
	 */
	protected $_debug = 50;

	/**
	 * @var Holds the Resource for DBs
	 */
	protected $_db;

	/**
	 * @var Database name
	 */
	protected $_dbName;

	/**
	 * @var Database config to use
	 */
	protected $_dbConfig;

	/**
	 * @var Whether object was saved.
	 */
	protected $_saved = false;

	/**
	 * @var Whether object was loaded.
	 */
	protected $_loaded = false;

	/**
	 * @var whether the model came from cache
	 */
	protected $_cache = false;

	/**
	 * @var This is to check whether an array was passed in for data.
	 */
	protected $_loaded_by_array = false;

	/**
	 * @var Holds list of all fields loaded from data array
	 */
	protected $_fields_loaded_by_array = array();

	/**
	 * Instantiates a model. If the model has been cached and $use_cache is true,
	 * cached version is used instead.
	 * 
	 * @param string $model 
	 * @param mixed $id 
	 * @param bool $use_cache 
	 * @return object
	 */
	public static function factory($model, $id=null)
	{
		$modelName = 'Model_' . ucfirst($model);
		if($id === null)
		{
			//if no ID is passed, no need to go further, just return the model
			return new $modelName;
		}

		$object = new $modelName;
		$object->load($id);

		return $object;
	}

	/**
	 * Constructor. Adds columns to the _data property
	 * @return void
	 */
	public function __construct()
	{
		//assign the initital data
		foreach($this->_columns AS $column)
		{
			$this->_data[$column] = null;
		}
	}

	/**
	 * Not currently used.
	 * @return void
	 */
	public function __destruct()
	{

	}

	/**
	 * Checks to see if data for $column is set.
	 * @param string $column 
	 * @return boolean
	 */
	public function __isset($column)
	{
		if(in_array($column, $this->_columns))
		{
			$data = $this->param($column);
			if(is_null($data) OR empty($data))
				return FALSE;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Unsets data for $column if it exists.
	 * @param string $column 
	 * @return boolean
	 */
	public function __unset($column)
	{
		if(in_array($column, $this->_columns))
		{
			$this->param($column, NULL);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Returns the value of the $column by using param()
	 * @param string $column 
	 * @return mixed
	 */
	public function __get($column)
	{
		$result = $this->param($column);
		
		//if it's null and it was loaded by array, reload the object
		if(is_null($result) AND $this->loaded() AND $this->loaded_by_array())
		{
			$this->load($this->pk());

			$result = $this->param($column);
		}

		return $result;
	}

	/**
	 * Sets the _data property with $value using param()
	 * @param string $column 
	 * @param mixed $value 
	 * @return void
	 */
	public function __set($column, $value)
	{
		$this->param($column, $value);
	}

	/**
	 * returns the class name :: primary key value
	 * @return string
	 */
	public function __toString()
	{
		return get_class($this) . '::' . $this->pk();
	}

	/**
	 * Returns the data of the object as an array.
	 * @return array
	 */
	public function as_array()
	{
		$data = array_merge($this->_data, $this->_data_changed);

		if ($this->_loaded_by_array)
		{
			$data = array_intersect_key($data, $this->_fields_loaded_by_array);
		}

		return $data;
	}

	/**
	 * Returns the value of the primary key.
	 * @return mixed
	 */
	public function pk()
	{
		if( ! $this->loaded())
			return null;
		
		return (string) $this->_data[$this->_primaryKey];
	}

	/**
	 * Load an object by passing the data as an array.
	 * @param array $data 
	 * @return Model
	 */
	public function load_array(array $data, $skip_check = FALSE)
	{
		foreach($this->_columns AS $column)
		{
			if (array_key_exists($column, $data))
			{
				$this->_fields_loaded_by_array[$column] = 1;

				$this->_data[$column] = $data[$column];
			}
		}

		if(array_key_exists($this->_primaryKey, $this->_data) AND ! empty($this->_data[$this->_primaryKey]))
		{
			if( ! $skip_check)
				$this->_loaded_by_array = FALSE;
			else
				$this->_loaded_by_array = TRUE;

			$this->_loaded = TRUE;
		}
		else
		{
			$this->_loaded = FALSE;
		}

		return $this;
	}

	/**
	 * Returns whether the object was loaded by array data.
	 * @return boolean
	 */
	public function loaded_by_array()
	{
		return $this->_loaded_by_array;
	}

	/**
	 * Returns an array with the DB instance and the name
	 * @return array
	 */
	public function db()
	{
		return array($this->_db, $this->_dbName, $this->_tableName);
	}

	/**
	 * Resets the model to an unloaded state with no data.
	 * @return Model
	 */
	public function reset()
	{
		//reset this object
		$this->_saved = $this->loaded = $this->_loaded_by_array = $this->_cache = FALSE;
		$this->_data = $this->_data_changed = $this->_fields_loaded_by_array = array();

		return $this;
	}

	/**
	 *
	 *
	 *
	 * @param	mixed		array or
	 * @return	mixed
	 */
	protected function _encrypt ($key, $value=NULL)
	{
		if ( is_array($key))
		{
			$data = $key;
			$encrypted_fields = array();

			foreach ($data AS $k => $value)
			{
				if ( array_key_exists($k, $this->_columns_crypt) )
				{
					$encrypted_fields[$k] = $this->_encrypt($k, $value);
				}
				else
				{
					$encrypted_fields[$k] = $value;
				}
			}

			return (count($encrypted_fields) > 0) ? $encrypted_fields : FALSE ;
		}

		if ( array_key_exists($key, $this->_columns_crypt) )
		{
			$owner_salt = $this->_columns_crypt[$key]['owner'];

			if ( ! array_key_exists($owner_salt, $this->_data) ) {
				throw new Cognac_Exception(
					'Invalid configuration, owner salt (:owner) does not exist in data.',
					array(':owner' => $owner_salt)
				);
			}

			if ( empty($this->_data[$owner_salt]) ) {
				throw new Cognac_Exception('Invalid configuration, owner salt can not be empty.');
			}

			$encryption = Encrypt::encode($value, $salt);

			return $encryption;
		}

		return $value;
	}

	protected function _decrypt ($field)
	{

	}

	final private function _decrypt_field ($key, $value, $salt)
	{

	}

	//these are implemented by the child model

	/**
	 * returns the columns to keep when serializing the object.
	 * @return array
	 */
	public function __sleep() {}

	/**
	 * Wakup method to reinitialize resources required by the object.
	 * @return void
	 */
	public function __wakeup() {}

	/**
	 * If $value is null, returns the value of $column in the object.
	 * If $value is not null, it will set the value of $column to $value.
	 * @param string $column 
	 * @param mixed $value 
	 * @return bool			
	 * @return mixed
	 */
	abstract public function param($column, $value=null);

	/**
	 * This will store the object using the appropriate driver.
	 * @return boolean
	 */
	abstract public function save();

	/**
	 * Returns whether the object has been saved.
	 * When changing the value of a column this is set to false.
	 * @return boolean
	 */
	abstract public function saved();

	/**
	 * Attempts to load object with primary key $id.
	 * @param mixed $id 
	 * @return Model
	 */
	abstract public function load($id);

	/**
	 * Returns whether the object is loaded or not.
	 * @return boolean
	 */
	abstract public function loaded();

	/**
	 * Deletes the object and removes the cached object using the appropriate driver.
	 * Returns false is the object was not removed.
	 * @return boolean
	 */
	abstract public function delete();
}
